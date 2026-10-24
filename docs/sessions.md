# Sessions

Tonka gère l'état conversationnel entre requêtes via un système de session pluggable (fichier ou base de données), accessible aussi bien via un helper global que depuis l'objet requête. Cette page couvre sa configuration, son API et les messages flash.

- [Sessions](#sessions)
- [Configuration](#configuration)
- [Drivers de Session](#drivers-de-session)
  - [File Driver](#file-driver)
  - [Database Driver](#database-driver)
- [Accès aux Sessions](#acces-aux-sessions)
  - [Via le Helper](#via-le-helper)
  - [Via la Requête](#via-la-requete)
  - [Via l'Instance](#via-l-39-instance)
- [Récapitulatif des Opérations](#recapitulatif-des-operations)
- [Messages Flash](#messages-flash)
  - [Définition](#definition)
  - [Lecture](#lecture)
- [Garbage Collection](#garbage-collection)
- [Cookies](#cookies)
  - [Lecture](#lecture-1)
  - [Écriture](#ecriture)
  - [Suppression](#suppression)
  - [Configuration des Cookies](#configuration-des-cookies)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Configuration

Le comportement de la session (driver, durées de vie) est défini dans un Service Provider dédié :

```php
// app/Providers/SessionServiceProvider.php
namespace App\Providers;

use Clicalmani\Core\Providers\SessionStorageServiceProvider as SessionProvider;

class SessionServiceProvider extends SessionProvider
{
    protected static string $driver = \Clicalmani\Core\Http\Session\FileSessionHandler::class;

    /**
     * Durée, en secondes, pendant laquelle la session peut rester inactive
     * avant d'être régénéré.
     *
     * @var int
     */
    protected static int $lifetime = 3000;

    /**
     * Durée maximale, en secondes, au-delà de laquelle la session expire
     * lorsqu'il n'y a pas d'activité.
     *
     * @var int
     */
    protected static int $max_lifetime = 9000;

    /**
     * Si activé, force l'expiration de la session à la fermeture du navigateur.
     *
     * @var bool
     */
    protected static bool $expire_on_close = false;

    /**
     * Map block defining browser tracking cookie policies and boundary visibility controls.
     * 
     * @var array
     */
    protected static array $cookie = [
        'name'      => '__SESSION_COOKIE__',
        'path'      => '/',
        'domain'    => '',
        'secure'    => false,
        'http_only' => true,
        'samesite'  => true
    ];

    /**
     * Amorçage
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }
}
```

## Drivers de Session

### File Driver

Stocke les sessions dans des fichiers sur le disque.

```php
// app/Providers/SessionServiceProvider.php

protected static string $driver = \Clicalmani\Core\Http\Session\FileSessionHandler::class;
```

**Structure des fichiers :**
```
storage/framework/sessions/
├── sess_tonka-abc123def456
├── sess_tonka-xyz789ghi012
└── sess_tonka-...
```

**Avantages :**
- ✅ Simple à configurer
- ✅ Aucune base de données nécessaire
- ✅ Performant pour les petits sites

**Inconvénients :**
- ❌ Non scalable horizontalement
- ❌ Pas de partage entre plusieurs serveurs
- ❌ Nettoyage à la charge du système de fichiers

### Database Driver

Stocke les sessions dans une base de données.

```php
// app/Providers/SessionServiceProvider.php

protected static string $driver = \Clicalmani\Core\Http\Session\DBSessionHandler::class;
```

**Structure de la table :**
```sql
CREATE TABLE sessions (
    id VARCHAR(191) PRIMARY KEY,
    access VARCHAR(100) NOT NULL,
    data LONGTEXT
);
```

**Avantages :**
- ✅ Scalable horizontalement
- ✅ Partageable entre plusieurs serveurs applicatifs
- ✅ Nettoyage automatisable via le [garbage collector](#garbage-collection)

**Inconvénients :**
- ❌ Requiert une base de données
- ❌ Plus lent que le driver fichier, chaque lecture/écriture de session impliquant une requête SQL

> **Recommandation :** pour tout déploiement sur plusieurs serveurs applicatifs (load balancing horizontal), le driver `File` devient inutilisable en l'état — chaque instance verrait des sessions différentes selon le serveur qui a traité la requête. Utilisez le driver `Database` (ou un backend partagé équivalent) dès que votre infrastructure dépasse un seul serveur.

---

## Accès aux Sessions

### Via le Helper

```php
// Lecture
$userId = session('user_id')->get();
$name = session()->get('name', 'Guest');

// Écriture
session()->set('user_id', auth()->id);

// Récupération de toutes les valeurs de session
$all = session()->all();

// Vérification
if (session('user_id')->exists()) {
    // ...
}

// Écriture de plusieurs valeurs à la fois
session()->fill([
    'user_id' => auth()->id,
    'user_name' => auth()->name()
]);

// Suppression d'une clé
session('user_id')->remove();

// Vider la session (efface les données, conserve la session active)
session()->flush();

// Détruire complètement la session (invalide également le SID)
session()->destroy();
```

### Via la Requête

```php
use Clicalmani\Core\Http\RequestInterface;

class UserController extends Controller
{
    public function profile(RequestInterface $request)
    {
        // Lecture
        $userId = $request->session()->get('user_id');
        $name = $request->session()->get('name', 'Guest');

        // Écriture
        $request->session()->set('user_id', auth()->id);
        $request->session()->set('user_name', auth()->name);

        // Vérification
        if ($request->session('user_id')->exists()) {
            // ...
        }

        // Suppression
        $request->session('user_id')->remove();

        // Écriture de plusieurs valeurs à la fois
        $request->session()->fill([
            'user_id' => auth()->id,
            'user_name' => auth()->name
        ]);

        // Vider
        $request->session()->flush();

        // Détruire la session
        $request->session()->destroy();
    }
}
```

### Via l'Instance

```php
class UserController extends Controller
{
    public function profile(RequestInterface $request)
    {
        $session = $request->session();

        // Lecture et écriture via accès dynamique aux propriétés
        $userId = $session->user_id;
        $session->last_seen = time();
    }
}
```

Cette forme est équivalente à `get()`/`set()`, mais plus concise pour un accès ponctuel à une clé unique déjà connue.

---

## Récapitulatif des Opérations

| Opération | Via le helper | Via la requête |
|---|---|---|
| Lire une valeur | `session('key')->get()` ou `session()->get('key', $default)` | `$request->session()->get('key', $default)` |
| Écrire une valeur | `session()->set('key', $value)` | `$request->session()->set('key', $value)` |
| Écrire plusieurs valeurs | `session()->fill([...])` | `$request->session()->fill([...])` |
| Vérifier l'existence | `session('key')->exists()` | `$request->session('key')->exists()` |
| Supprimer une clé | `session('key')->remove()` | `$request->session('key')->remove()` |
| Vider la session | `session()->flush()` | `$request->session()->flush()` |
| Détruire la session | `session()->destroy()` | `$request->session()->destroy()` |

`flush()` efface les données stockées tout en conservant une session active (et son identifiant) ; `destroy()` va plus loin en invalidant la session elle-même — après quoi un nouvel identifiant de session sera émis à la requête suivante.

---

## Messages Flash

### Définition

Un message flash est une donnée de session qui ne survit qu'à la requête suivant celle où elle a été définie — typiquement utilisé pour afficher une confirmation après une redirection consécutive à un traitement (création, mise à jour, suppression).

```php
// Définition lors d'une redirection
return redirect('/users')->with('success', 'Utilisateur créé avec succès !');
```

### Lecture

```php
// Lecture au chargement de la page suivante
$successMessage = session()->get('success');
```

```twig
{# resources/views/layouts/app.twig #}
{% if session_has_flash('success') %}
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
{% endif %}

{% if session_has_flash('error') %}
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
{% endif %}
```

---

## Garbage Collection

La purge des sessions expirées s'appuie directement sur les deux propriétés définies dans le [Service Provider de Session](#service-provider-de-session) : une session est éligible à la suppression dès que sa durée d'inactivité dépasse `$lifetime`, ou que son âge total dépasse `$max_lifetime` — selon la limite atteinte en premier.

Avec le driver `File`, cette purge dépend du garbage collector natif de PHP (`session.gc_probability` / `session.gc_divisor` dans `php.ini`), déclenché de façon probabiliste à chaque requête. Avec le driver `Database`, la purge des lignes expirées est gérée automatiquement.

---

## Cookies

### Lecture
<div id="lecture-1"></div>

```php
// Via la requête
$theme = $request->cookie()->get('theme', 'light');
$language = $request->cookie()->get('language', 'fr');

// Via le helper
$theme = cookie()->get('theme', 'light');

// Vérification
if ($request->cookie('theme')->exists()) {
    // ...
}

// Récupération de tous les cookies
$allCookies = cookie()->all();
```

### Écriture

```php
// Via la requête
$request->setCookie('theme', 'dark', 60 * 24 * 30);
$request->setCookie('language', 'fr', 60 * 24);

// Via le helper
cookie('theme', 'dark', 60 * 24 * 30)->set();
cookie('language', 'fr', 60 * 24)->set();

// Via la réponse
return response()
    ->cookie('theme', 'dark', 60 * 24 * 30)
    ->cookie('language', 'fr', 60 * 24)
    ->redirect('/settings');
```

### Suppression

```php
// Via la requête
$request->cookie('theme')->delete();

// Via la réponse
return response()
    ->deleteCookie('theme')
    ->deleteCookie('language')
    ->redirect('/settings');
```

### Configuration des Cookies

!> **Rappel de sécurité :** pour tout cookie porteur d'une information sensible (session, jeton d'authentification persistant), les drapeaux `secure` (HTTPS uniquement) et `http_only` (inaccessible depuis JavaScript) doivent rester activés en production — c'est la principale défense contre le vol de cookie par une faille XSS.

---

## Exemple Complet

### Contrôleur

```php
namespace App\Http\Controllers;

use Clicalmani\Core\Http\RequestInterface;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login(RequestInterface $request)
    {
        if (auth()->attempt($request->only(['email', 'password']))) {
            $user = auth()->user();

            // Session
            $request->session()->set('user_id', $user->id);
            $request->session()->set('user_name', $user->name);
            $request->session()->set('user_role', $user->role);
            $request->session()->set('last_login', time());

            // Cookie
            $request->setCookie('user_id', $user->id, 60 * 24 * 30);
            $request->setCookie('user_name', $user->name, 60 * 24 * 30);

            // Journalisation
            logger()->info('User logged in', ['user_id' => $user->id]);

            return redirect('/dashboard');
        }

        return back()->withInput($request->only(['email']));
    }

    public function logout(RequestInterface $request)
    {
        // On capture l'identifiant avant de purger la session, pour pouvoir
        // journaliser la déconnexion une fois les données de session effacées.
        $userId = $request->session()->get('user_id');

        // Session
        $request->session('user_id')->remove();
        $request->session('user_name')->remove();
        $request->session('user_role')->remove();
        $request->session('last_login')->remove();
        $request->session()->destroy();

        // Cookies
        $request->cookie('user_id')->delete();
        $request->cookie('user_name')->delete();

        // Journalisation
        logger()->info('User logged out', ['user_id' => $userId]);

        return redirect('/login');
    }

    public function profile(RequestInterface $request)
    {
        if (!$request->session('user_id')->exists()) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $userId = $request->session()->get('user_id');
        $user = User::find($userId);
        $lastLogin = $request->session()->get('last_login');

        return Inertia::render('Profile', [
            'user' => $user,
            'last_login' => $lastLogin ? date('Y-m-d H:i', $lastLogin) : null,
            'theme' => $request->cookie()->get('theme', 'light'),
            'language' => $request->cookie()->get('language', 'fr')
        ]);
    }
}
```

### Vue Twig

```twig
{# resources/views/layouts/app.twig #}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tonka App</title>
</head>
<body>
    {# Messages flash #}
    {% if session_has_flash('success') %}
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    {% endif %}

    {% if session_has_flash('error') %}
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    {% endif %}

    {# Contenu #}
    <div class="container">
        {% block content %}{% endblock %}
    </div>
</body>
</html>
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les sessions :

1. **[Base de Données](database.md)** → Travailler avec la base de données
2. **[Elegant ORM](orm.md)** → Approfondir l'ORM
3. **[Authentification](authentication.md)** → Gérer l'authentification

---

**Sessions maîtrisées !** 🚀 → [Base de Données](database.md)