# Réponses

Cette page recense les différentes façons de construire une réponse HTTP dans Tonka — JSON, vues Inertia, redirections, fichiers, cookies et en-têtes — et les situations où chacune est la plus appropriée.

- [Réponses](#reponses)
- [Types de Réponses](#types-de-reponses)
- [Réponses JSON](#reponses-json)
  - [JSON Simple](#json-simple)
  - [Réponse Success](#reponse-success)
  - [Réponse Error](#reponse-error)
- [Réponses Inertia (Vues/React)](#reponses-inertia-vues-react-)
  - [Rendu Simple](#rendu-simple)
  - [Partage de Données](#partage-de-donnees)
  - [Version et Cache](#version-et-cache)
- [Redirections](#redirections)
  - [Redirection Simple](#redirection-simple)
  - [Redirection avec Messages Flash](#redirection-avec-messages-flash)
  - [Redirection avec Données](#redirection-avec-donnees)
  - [Redirection vers une Route Nommée](#redirection-vers-une-route-nommee)
  - [Redirection Inertia](#redirection-inertia)
- [Fichiers](#fichiers)
  - [Téléchargement](#telechargement)
  - [Streaming](#streaming)
- [Codes d'Erreur](#codes-derreur)
- [Cookies](#cookies-1)
  - [Définir un Cookie](#definir-un-cookie)
  - [Supprimer un Cookie](#supprimer-un-cookie)
- [En-têtes HTTP](#en-tetes-http)
  - [Ajouter des En-têtes](#ajouter-des-en-tetes)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Types de Réponses

**Tonka** propose plusieurs types de réponses, chacun adapté à un contexte de consommation différent :

| Type | Méthode | Description |
|------|---------|-------------|
| **JSON** | `response()->json()` | Réponse JSON |
| **Vue** | `Inertia::render()` | Vue Inertia (React/Vue) |
| **Vue Twig** | `view()` | Vue Twig |
| **Redirection** | `redirect()` | Redirection HTTP |
| **Fichier** | `response()->sendFile()` | Téléchargement |
| **Stream** | `response()->stream()` | Streaming |
| **Erreur** | `response()->notFound()` | Code d'erreur |
| **Statut HTTP** | `response()->sendStatus()` | Envoi d'un statut HTTP |
| **En-tête** | `response()->sendHeader()` | En-tête HTTP |
| **HTML** | `response()->send()` | HTML brut |

---

## Réponses JSON

### JSON Simple

```php
// Réponse JSON standard
return response()->json([
    'success' => true,
    'data' => $users
]);

// Réponse JSON avec code d'état explicite
return response()->status(404)->json([
    'error' => 'Not found'
]);
```

### Réponse Success

```php
// Réponse de succès avec message
return response()->success('User created successfully!');

// Réponse de succès avec données structurées
return response()->success(['user' => $user]);
```

### Réponse Error

```php
// Réponse d'erreur
return response()->error('User not found');
```

`success()` et `error()` encapsulent une enveloppe JSON cohérente (statut, message, données) sans qu'il soit nécessaire de la reconstruire à la main à chaque endpoint — un contrat de réponse homogène simplifie considérablement le traitement côté client, notamment pour une application front-end consommant plusieurs endpoints API.

---

## Réponses Inertia (Vues/React)

### Rendu Simple

```php
use Inertia\Inertia;

// Rendu d'un composant React/Vue
return Inertia::render('User.Index', [
    'users' => $users,
    'filters' => $filters
]);

// Via le helper
return inertia('User.Index');
```

> **Pour les développeurs Laravel :** l'intégration Inertia de Tonka (`Inertia::render`, `Inertia::share`, `Inertia::version`) reprend directement l'API du package `inertiajs/inertia-laravel` — si vous avez déjà travaillé avec une stack Laravel + Inertia, cette section ne présente aucune surprise.

### Partage de Données

```php
// app/Providers/AppServiceProvider.php

public function boot(): void
{
    Inertia::share([
        'appName' => config('app.name'),
        'user' => auth()->id // Identifiant de l'utilisateur authentifié
    ]);
}

// Dans le contrôleur
return Inertia::render('User.Index', [
    'users' => $users
])->withViewData([
    'metaTitle' => 'Liste des utilisateurs'
]);
```

Les données partagées via `Inertia::share()` sont injectées dans **chaque** réponse Inertia de l'application (souvent utile pour l'utilisateur courant, la configuration, les traductions) — à ne pas confondre avec `withViewData()`, qui n'affecte que la réponse courante et n'est pas transmis au composant front-end en tant que prop, mais fusionné aux métadonnées de la vue racine.

### Version et Cache

```php
// app/Providers/AppServiceProvider.php

use Inertia\Inertia;

public function register(): void
{
    // Définir une version fixe des assets
    Inertia::version('1.0.0');

    // Version dynamique, dérivée du manifeste de build
    Inertia::version(md5_file(public_path('build/manifest.json')));
}
```

Le mécanisme de versionnement permet à Inertia de détecter qu'un nouveau build front-end a été déployé (le hash change) et de forcer un rechargement complet côté client plutôt qu'une navigation SPA — évitant ainsi qu'un utilisateur reste bloqué sur des assets JavaScript obsolètes après une mise en production.

---

## Redirections

### Redirection Simple

```php
// Helper redirect
return redirect('/users');

// Depuis l'objet réponse
return response()->redirect('/users');

// Retour à la page précédente
return back();
```

### Redirection avec Messages Flash

```php
// Message de succès
return redirect('/users')->with('success', 'User created!');
return redirect('/users')->withSuccess('User created!');

// Message d'erreur
return redirect('/users')->with('error', 'User not found');
return redirect('/users')->withError('User not found');

// Message d'information
return redirect('/users')->with('info', 'User updated');

// Message d'avertissement
return redirect('/users')->with('warning', 'User deleted');
```

Ces messages sont stockés en session le temps d'une seule requête (voir [Messages Flash](requests.md#messages-flash)) — `withSuccess()`/`withError()` sont de simples raccourcis pour `with('success', ...)`/`with('error', ...)`.

### Redirection avec Données

```php
// Redirection avec les données précédemment soumises (old input)
return redirect('/users')->withInput($request->all());

// Redirection avec un sous-ensemble de données
return redirect('/users')->withInput([
    'name' => $request->name,
    'email' => $request->email
]);
```

`withInput()` est particulièrement utile après l'échec d'une validation : la page cible peut repeupler le formulaire avec les valeurs précédemment saisies, sans obliger l'utilisateur à tout ressaisir.

### Redirection vers une Route Nommée

```php
// Vers une route nommée
return redirect()->route('users.index');

// Vers une route nommée avec paramètres
return redirect()->route('posts.index', ['category' => 'tech']);
```

### Redirection Inertia

```php
// Redirection déclenchant une navigation complète du navigateur
return Inertia::location('/login');
```

> **Point d'attention :** contrairement à `redirect()->route(...)`, qui reste dans le cycle de vie SPA d'Inertia, `Inertia::location()` provoque un rechargement complet de la page. Réservez cet appel aux cas où une sortie du contexte Inertia est réellement nécessaire — redirection vers un fournisseur d'authentification externe, par exemple.

---

## Fichiers

### Téléchargement

```php
// Téléchargement simple
return response()->sendFile(
    storage_path('documents/report.pdf'),
    'rapport.pdf'
);

// Avec type MIME explicite
return response()->sendFile(
    storage_path('documents/report.pdf'),
    'rapport.pdf',
    'application/pdf'
);
```

### Streaming

```php
// Streaming d'une vidéo
return response()->stream(
    storage_path('videos/presentation.mp4')
);

// Streaming avec en-têtes personnalisés
return response()->stream(
    storage_path('videos/presentation.mp4'),
    200,
    ['Content-Disposition' => 'inline']
);

// Réponse partielle (contenu par plage, ex. lecture vidéo avec avance rapide)
return response()->stream(
    storage_path('videos/presentation.mp4')
)->status(206);
```

Le code `206 Partial Content` signale au client que seule une plage d'octets du fichier est renvoyée (en-tête `Range` de la requête) — indispensable pour permettre à un lecteur vidéo HTML5 de se positionner à un instant donné sans retélécharger l'intégralité du fichier.

---

## Codes d'Erreur

```php
// 404 - Not Found
return response()->notFound();

// 403 - Forbidden
return response()->forbidden();

// 401 - Unauthorized
return response()->unauthorized();

// 500 - Internal Server Error
return response()->internalServerError();

// Code personnalisé
return response()->status(429)->json([
    'error' => 'Too many requests'
]);

// Via le helper abort
abort(404);
abort(403, 'Access denied');
```

Les méthodes nommées (`notFound()`, `forbidden()`...) sont des raccourcis lisibles pour les statuts les plus courants ; `status()` reste la voie générique pour tout code non couvert par un raccourci dédié.

---

## Cookies

### Définir un Cookie

```php
// Via la réponse
return response()
    ->cookie('theme', 'dark', 60 * 24 * 30) // 30 jours
    ->cookie('language', 'fr', 60 * 24)     // 24 heures
    ->redirect('/settings');

// Cookie sécurisé
// Signature : name, value, minutes, path, domain, secure, httpOnly
return response()
    ->cookie('remember_token', $token, 60 * 24 * 365, '/', null, true, true)
    ->redirect('/');
```

Pour un cookie porteur d'un jeton d'authentification persistant (`remember_token`), les drapeaux `secure` (envoi uniquement en HTTPS) et `httpOnly` (inaccessible depuis JavaScript) doivent systématiquement être activés — c'est votre principale défense contre le vol de jeton par XSS.

### Supprimer un Cookie

```php
// Via la réponse
return response()
    ->deleteCookie('theme')
    ->deleteCookie('remember_token')
    ->redirect('/settings');
```

---

## En-têtes HTTP

### Ajouter des En-têtes

```php
// En-tête simple
return response()->json($data)
    ->header('X-Custom-Header', 'value');

// En-têtes multiples, chaînés
return response()->json($data)
    ->header('X-Custom-Header', 'value')
    ->header('X-Another-Header', 'another');

// Via withHeaders, pour un ensemble d'en-têtes défini en une fois
return response()->json($data)
    ->withHeaders([
        'X-Custom-Header' => 'value',
        'X-Another-Header' => 'another',
        'Cache-Control' => 'public, max-age=3600'
    ]);
```

---

## Exemple Complet

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Clicalmani\Core\Http\RequestInterface;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs (Inertia)
     */
    public function index(Request $request)
    {
        $users = User::paginate(
            $request->input('page', 1),
            $request->input('per_page', 10)
        );

        return Inertia::render('User/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page'])
        ]);
    }

    /**
     * API - Liste des utilisateurs (JSON)
     */
    public function apiIndex(Request $request)
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'data' => $users,
            'meta' => [
                'total' => $users->count(),
                'timestamp' => time()
            ]
        ]);
    }

    /**
     * Création d'un utilisateur avec redirection
     */
    public function store(Request $request)
    {
        $user = User::create($request->validated());

        return redirect('/users')
            ->with('success', 'User created successfully!')
            ->withInput($request->only(['name', 'email']));
    }

    /**
     * Téléchargement d'un fichier
     */
    public function download($id)
    {
        $user = User::find($id);

        if (!$user->avatar) {
            return response()->notFound();
        }

        $path = storage_path('avatars/' . $user->avatar);

        if (!file_exists($path)) {
            return response()->notFound();
        }

        return response()->sendFile(
            $path,
            'avatar_' . $user->id . '.jpg'
        );
    }

    /**
     * API - Mise à jour (JSON)
     */
    public function apiUpdate(Request $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->error('User not found');
        }

        $user->update($request->all());

        return response()->success([
            'user' => $user,
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Suppression (JSON)
     */
    public function apiDestroy(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->error('User not found');
        }

        $user->delete();

        return response()->success('User deleted successfully');
    }

    /**
     * Suppression avec redirection
     */
    public function destroy(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return back()->with('error', 'User not found');
        }

        $user->delete();

        return redirect('/users')
            ->with('success', 'User deleted successfully');
    }
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les réponses :

1. **[Middlewares](middleware.md)** → Protéger vos routes
2. **[Vues](views.md)** → Approfondir les vues Inertia
3. **[Validation](validation.md)** → Valider les données
4. **[Base de Données](database.md)** → Travailler avec les modèles

---

**Réponses comprises !** 🚀 → [Middlewares](middleware.md)