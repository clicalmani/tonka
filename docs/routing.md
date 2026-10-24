# Routing

Le système de routing de Tonka emprunte des concepts familiers aux écosystèmes **Laravel** (syntaxe fluide, resource controllers, route model binding) et expose un pipeline de middlewares comparable à celui de **Symfony** (`HttpKernel`, PSR-15). Cette page détaille son fonctionnement en profondeur, avec des repères ciblés pour les développeurs venant de l'un ou l'autre de ces frameworks.

- [Routing](#routing)
- [Définition des Routes](#definition-des-routes)
  - [Fichiers et passerelles](#fichiers-et-passerelles)
  - [Méthodes HTTP Supportées](#methodes-http-supportees)
  - [Routes avec Closure](#routes-avec-closure)
  - [Routes vers Contrôleur](#routes-vers-controleur)
  - [Inspecter les routes](#inspecter-les-routes)
- [Paramètres de Route](#parametres-de-route)
  - [Paramètres Obligatoires](#parametres-obligatoires)
  - [Paramètres Optionnels](#parametres-optionnels)
  - [Validation des Paramètres](#validation-des-parametres)
  - [Prévention de la Manipulation de Route](#prevention-de-la-manipulation-de-route)
- [Fonctionnement Interne](#fonctionnement-interne)
  - [Construction des Routes](#construction-des-routes)
  - [Algorithme de Matching](#algorithme-de-matching)
  - [Priorité et Doublons](#priorite-et-doublons)
- [Routes Nommées](#routes-nommees)
  - [Définition](#définition)
  - [Génération d'URL](#generation-durl)
- [Groupes de Routes](#groupes-de-routes)
- [Resource Controllers](#resource-controllers)
- [Middlewares](#middlewares)
- [Route Model Binding](#route-model-binding)
- [Builders Personnalisés](#builders-personnalises)
- [Bonnes Pratiques](#bonnes-pratiques)
- [Prochaines Étapes](#prochaines-etapes)

---

## Définition des Routes

### Fichiers et passerelles

Les routes sont définies dans le dossier `routes/`. Chaque requête est traitée par l'une des deux **passerelles** :

| Passerelle | Quand | Middlewares globaux |
|---|---|---|
| `web` | par défaut | ceux de `Web` (session, cookies, CSRF…) |
| `api` | l'URL commence par le préfixe configuré (`route.api_prefix`) | ceux de `Api` (rate limiting, jeton JWT…) |

La passerelle est déterminée **par l'URL de la requête**.

```php
// routes/web.php

use Clicalmani\Core\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', fn() => inertia('Home'));

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/:id', [UserController::class, 'show']);
Route::put('/users/:id', [UserController::class, 'update']);
Route::delete('/users/:id', [UserController::class, 'destroy']);
```

```php
// routes/api.php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
```

Chacune des deux passerelles peut avoir des sous-passerelles. Cet aspect permet de mieux organiser les routes dans des fichiers différents selon la logique du code. Un sous passerelle est délimité par un middleware qui s'applique automatiquement à toutes les routes qu'elle contient. Les développeurs Laravel et Symfony peuvent sauter cette section pour aller directement sur [middlewares](#middlewares).

> **Pour les développeurs Laravel :** la distinction `web` / `api` et son couplage à un groupe de middlewares par défaut reproduit le fonctionnement de Laravel.

### Méthodes HTTP Supportées

```php
Route::get($uri, $action);      // GET
Route::post($uri, $action);     // POST
Route::put($uri, $action);      // PUT
Route::patch($uri, $action);    // PATCH
Route::delete($uri, $action);   // DELETE
Route::options($uri, $action);  // OPTIONS
Route::match(['GET', 'POST'], $uri, $action); // Combinaison de méthodes
```

`PUT` implique un remplacement **complet** de la ressource (idempotent), `PATCH` une modification **partielle**. Tonka ne force pas cette distinction : c'est votre contrôleur qui doit la respecter.

`Route::match()` enregistre la même URI pour chaque verbe demandé. Un nom donné à la route [(`->name()`)](#routes-nommées) est partagé par ces verbes.

### Routes avec Closure

```php
Route::get('/', fn() => response()->send('Hello World'));

Route::get('/user', function() {
    $user = User::find(1);
    return view('profile', ['user' => $user]);
});
```

### Routes vers Contrôleur

```php
// Méthode spécifique
Route::get('/users', [UserController::class, 'index']);

// Contrôleur invocable (Invokable Controller)
Route::get('/dashboard', DashboardController::class);

// Syntaxe chaîne de caractères
Route::get('/users', 'UserController@index');
```

La syntaxe *invokable* suppose que le contrôleur expose une méthode `__invoke()`. La syntaxe `'UserController@index'` est résolue contre `App\Http\Controllers`.

> **Pour les développeurs Symfony :** cette résolution par nom de classe tient lieu d'équivalent aux attributs `#[Route]`. Tonka centralise la déclaration dans `routes/` plutôt que dans les classes.

### Inspecter les routes

```php
Route::all();      // URI de toutes les routes enregistrées
Route::current();  // Route de la requête courante (ou null)
Route::uri();      // Chemin de la requête courante
```

---

## Paramètres de Route

### Paramètres Obligatoires

```php
// Style Tonka (par défaut)
Route::get('/users/:id', [UserController::class, 'show']);

// Multiples paramètres
Route::get('/users/:id/posts/:post_id', [PostController::class, 'show']);
```

La syntaxe `:id` est celle du **builder par défaut** de Tonka. La syntaxe `{id}` façon Laravel n'est disponible que si un builder alternatif est configuré (voir [Builders Personnalisés](#builders-personnalisés)) — les deux syntaxes ne coexistent pas nativement dans le même projet.


### Paramètres Optionnels

```php
Route::get('/users/?:id', [UserController::class, 'show']);
```

Une route optionnelle est **développée à l'enregistrement** en plusieurs routes ordinaires, une par variante. Les optionnels **adjacents** se retirent par la droite ; des optionnels séparés par un segment statique se combinent librement.

| Déclaration | Routes générées |
|---|---|
| `/users/?:id` | `/users`, `/users/:id` |
| `/users/?:user/posts` | `/users/posts`, `/users/:user/posts` |
| `/a/:id/?:x` | `/a/:id`, `/a/:id/:x` |
| `/a/?:x/?:y` | `/a`, `/a/:x`, `/a/:x/:y` |
| `/a/?:x/b/?:y` | `/a/b`, `/a/:x/b`, `/a/b/:y`, `/a/:x/b/:y` |

`/a/?:x/?:y` ne génère **pas** `/a/:y` : cette variante aurait la même forme que `/a/:x` et le routeur ne pourrait pas les distinguer.

Ce que cela implique :

- **Les méthodes chaînées s'appliquent à toutes les variantes.** `->name()`, `->whereNumber()` et les autres validations portent sur l'ensemble des routes générées.
- **Le nombre de variantes croît** avec les optionnels séparés (jusqu'à 2ⁿ). Au-delà de deux ou trois, préférez des routes distinctes.
- **Un optionnel au milieu** (`/users/?:user/posts`) est légitime, notamment pour les [ressources imbriquées](#ressources-imbriquées).
- **La génération d'URL** choisit la variante selon les paramètres fournis (voir [Génération d'URL](#génération-durl)).

### Validation des Paramètres

#### Méthodes de Validation

```php
// Validation numérique
Route::get('/users/:id', [UserController::class, 'show'])->whereNumber('id');

// Validation entière
Route::get('/users/:id', [UserController::class, 'show'])->whereInt('id');

// Validation flottante
Route::get('/products/:price', [ProductController::class, 'show'])->whereFloat('price');

// Validation par pattern regex
Route::get('/posts/:slug', [PostController::class, 'show'])
    ->wherePattern('slug', '^[a-z0-9-]+$');

// Validation par modèle (existence en base)
Route::get('/users/:id', [UserController::class, 'show'])
    ->whereModel('id', User::class);

// Validation énumération
Route::get('/users/:status', [UserController::class, 'index'])
    ->whereEnum('status', ['active', 'inactive', 'pending']);

// Validation IN (liste de valeurs)
Route::get('/products/:category', [ProductController::class, 'index'])
    ->whereIn('category', ['electronics', 'books', 'clothing']);

// Plusieurs paramètres avec la même règle
Route::get('/users/:id/posts/:post_id', [PostController::class, 'show'])
    ->whereNumber(['id', 'post_id']);
```

| Méthode | Règle appliquée | Description |
|---|---|---|
| `whereNumber`, `whereNum` | `numeric` | Nombre |
| `whereInt` | `int` | Entier strict |
| `whereFloat` | `float` | Décimal strict |
| `wherePattern` | `regexp\|pattern:expression` | Expression régulière |
| `whereEnum` | `enum\|list:a,b,…` | Liste de valeurs autorisées |
| `whereIn` | `required\|in\|list:a,b,…` | Liste de valeurs autorisées |
| `whereToken` | `token` | Jeton |
| `whereModel` | `required\|id\|model:Nom` | Existence en base via le modèle |

Ces méthodes agissent **pendant le matching**, pas après. Une route qui échoue à une contrainte n'est pas dispatchée : le routeur la considère comme non-matchée et évalue les routes suivantes (ou renvoie une **404**). C'est différent d'une validation de `FormRequest`, qui s'exécute après résolution de la route et retourne un **422**.

> **Attention pour `whereModel`.** Elle effectue une **requête en base pendant le matching**, et ce **pour chaque route candidate** de même forme évaluée, pas une seule fois.

**Une seule contrainte par paramètre.** Appliquer deux contraintes au même paramètre ne les cumule pas : **la dernière remplace la précédente**. Pour combiner, utilisez une règle composée :

```php
Route::get('/users/:id', [UserController::class, 'show'])
    ->where('id', 'required|numeric|min:1|max:1000');
```

```php
// ✗ whereNumber est remplacée par whereIn
->whereNumber('id')->whereIn('id', ['alpha']);
```

#### Contraintes Globales

```php
// app/Providers/RouteServiceProvider.php
public function boot(): void
{
    // Pattern global pour tous les paramètres 'id'
    Route::pattern('id', '[0-9]+');

    // Plusieurs paramètres à la fois
    Route::validate(['slug', 'code'], ['[a-z-]+', '[A-Z0-9]+']);
}
```

Un pattern global s'applique à **tout paramètre portant ce nom**, à toutes les routes **créées après sa déclaration**. Une contrainte déclarée sur la route (`->where…`) remplace le pattern global pour ce paramètre.

#### Validation Personnalisée

```php
// Avec la méthode where()
Route::get('/users/:id', [UserController::class, 'show'])
    ->where('id', 'required|numeric|min:1|max:1000');
```

`where()` accepte la même syntaxe de règles que le validateur de requêtes (`required|numeric|min:...`), ce qui permet d'exprimer des contraintes plus explicites (`min`/`max`) qu'un simple appel à l'une des méthodes `whereX`.

### Prévention de la Manipulation de Route

Au-delà de la validation de *type* ou de *format* vue ci-dessus, Tonka propose un mécanisme d'intégrité dédié aux routes : la vérification par empreinte (*hash* - exposant un identifiant sensible dans l'URI) ou la sécurisation dans la session active, portée par le middleware `PreventRouteTampering`.

#### Empreinte Hash

Pour sécuriser une route par une empreinte **Hash** contre toute falsification, Tonka met à votre disposition la méthode `protectHash()` que vous pouvez chaîner dans la déclaration de la route. 

```php
use App\Http\Middlewares\PreventRouteTampering;

Route::get('/users/:id', [UserController::class, 'edit'])->name('users.edit')
->where('id', 'required|id|model:user')
->protectHash();
```

Le principe : la route intègre un segment `:hash`, généré côté serveur via le helper `create_parameters_hash()`, qui prend en argument un tableau associatif où chaque clé correspond au nom d'un paramètre de la route à protéger, et chaque valeur au contenu attendu de ce paramètre. Tonka compare ensuite, à chaque requête, l'empreinte reçue à celle recalculée à partir des valeurs effectivement transmises.

```php
// App\Http\Controllers\UserController
use Clicalmani\Core\Resources\ViewInterface;
use Clicalmani\Core\Http\ResponseInterface;
use Clicalmani\Core\Http\RequestInterface;

public function store(int $id): ViewInterface
{
    // Store user

    return redirect()->route('users.edit', [
        'id' => $id,
        'hash' => create_parameters_hash(['id' => $id])
    ]);
}

public function update(RequestInterface $request, int $id): ResponseInterface
{
    // Logique métier
}
```

Lorsqu'un utilisateur accède à la page `users.edit` via un lien signé, par exemple `/users/1/ED8UI90`, Tonka valide le paramètre `:id` par rapport à l'empreinte générée. Si l'identifiant est modifié manuellement dans la barre d'adresse (`1` → `2`), la vérification échoue côté contrôleur, sans que l'empreinte — recalculée à partir de la nouvelle valeur — ne corresponde plus à celle transmise.

!> Le paramètre hash n'est pas figé, il peut être personnalisé dans le fichier de configuration `config/routing.php`.

#### Jeton CSRF

Pour profiter de la protection par un jeton CSRF générique, Tonka met à votre disposition la méthode `protect()`. Elle fait pratiquement la même chose que `protectHash()` mais de manière différente : Au lieu d'attendre une empreinte **hash** dans l'url, il s'appuis sur un jeton CSRF générique stocké dans la session. Ce processus de protection par jeton est activé automatiquement à chaque fois que vous allez devoir resoudre une route protégée par la méthode `route()` de la classe `Redirect` ou par le helper `route()`.

```php
use App\Http\Middlewares\PreventRouteTampering;

Route::get('/users/:id', [UserController::class, 'edit'])->name('users.edit')
->where('id', 'required|id|model:user')
->protect();

// App\Http\Controllers\UserController
use Clicalmani\Core\Resources\ViewInterface;
use Clicalmani\Core\Http\ResponseInterface;
use Clicalmani\Core\Http\RequestInterface;

public function store(int $id): ViewInterface
{
    // Store user

    // Déclenche la protection par jeton
    return redirect()->route('users.edit', [
        'id' => $id,
        'hash' => create_parameters_hash(['id' => $id])
    ]);
}
```

!> **Pour les développeurs Laravel :** ce mécanisme est l'équivalent conceptuel des *signed routes* (`URL::signedRoute`).

```php
// config/hashing.php
return [
    // ...
    'hash_length' => 10,
    'hash_parameter' => 'hash',
]
```

## Fonctionnement Interne

### Construction des Routes

Tonka **ne met pas ses routes en cache**. À chaque requête, les routes du verbe (pas toutes les routes) son chargées en memoir : chaque `Route::get()` crée une `Route` (faite de `Segment`s), son `Validator`, et l'enregistre dans le `Registry`. Puis par élimination, les routes de taille différentes sont écartées, ce qui reduit considerablement le nombre de routes en memoir.

Conséquences :

- **Pas de cache périmé** après un déploiement, et rien à invalider.
- **Les closures sont permises partout**, puisque rien n'est sérialisé.

### Algorithme de Matching

Pour une requête `GET /users/5`, le routeur procède ainsi :

1. **Filtre par verbe** : seules les routes du verbe de la requête sont considérées.
2. **Filtre par longueur** : seules les routes ayant autant de segments que l'URL restent candidates.
3. **Contrôle des doublons** : deux candidates de même URI et de mêmes validateurs lèvent une `DuplicateRouteException`.
4. **Sélection** :
   - s'il n'y a aucune valeur, seules les routes **sans paramètre** peuvent correspondre ;
   - sinon, chaque candidate reçoit les valeurs, ses validateurs s'exécutent, et la **première** candidate qui passe et dont la forme correspond à l'URL est retenue.
5. Les paramètres de la route retenue sont rendus disponibles via `$_REQUEST` (et donc la requête).

### Priorité et Doublons

| Situation | Résultat |
|---|---|
| `/users/create` et `/users/:id` ; URL `/users/create` | la route **statique** |
| `/users/create` et `/users/:id` ; URL `/users/5` | la route **paramétrée** |
| `/orders/:id` ; URL `/orders/orders` | la route paramétrée (`id` = `orders`) |
| `/items/:id` (`whereNumber`) et `/items/:slug` (`whereIn`) | départagées par la **validation** |
| `/items/:id` et `/items/:slug`, sans validateurs | la **première enregistrée** gagne, sans avertissement |
| même URI deux fois, sans validateurs | `DuplicateRouteException` |
| même URI deux fois, validateurs différents | acceptées, départagées par la validation |

Deux routes qui ne diffèrent que par le **nom** de leur paramètre ne sont pas des doublons pour le routeur : donnez-leur des validateurs distincts, sans quoi la seconde ne sera jamais atteinte.

---

## Routes Nommées

```php
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/:id', [UserController::class, 'show'])->name('users.show');
Route::get('/posts/:slug', [PostController::class, 'show'])->name('posts.show');
```

Un nom est **unique par verbe** : en réutiliser un lève une exception, y compris pour une [route optionnelle](#paramètres-optionnels) (dont toutes les variantes partagent le même nom). Un même nom peut en revanche servir sur plusieurs verbes (`Route::match()`).

La convention `ressource.action` n'est pas imposée, mais recommandée.

### Génération d'URL

```php
namespace App\Http\Controllers;

class AppController extends Controller
{
    public function show(int $id): ViewInterface
    {
        return redirect()->route('users.show', ['id' => $id]);
    }
}
```

La méthode `route()` effectue une **résolution inverse** : à partir du nom et des paramètres fournis, elle reconstruit l'URI en réinjectant les valeurs dans le motif compilé de la route. Si un paramètre requis par le motif n'est pas fourni, une exception est levée à la génération plutôt que de produire silencieusement une URL invalide — un comportement volontairement strict, préférable à un lien mort en production.

| Appel | Résultat |
|---|---|
| `route('users.show', ['id' => 5])` | `/users/5` |
| `route('posts.show', 5, 9)` (positionnel) | `/users/5/posts/9` |
| `route('users.show', [5])` (liste = positionnel) | `/users/5` |
| `route('users.show')` | **`InvalidArgumentException`** : paramètre `id` manquant |
| `route('users.show', ['id' => ''])` | idem : une valeur `null` ou vide équivaut à un paramètre absent |
| `route('users.show', ['id' => 'a b'])` | `/users/a%20b` (valeurs encodées) |
| `route('home')` (route `/`) | `/` |
| `route('inconnu')` | `null` |

**Routes optionnelles.** La variante retenue est celle qui **consomme le plus de paramètres fournis** :

```php
Route::get('/checkin/?:date', 'checkin')->name('checkin');

route('checkin');                          // '/checkin'
route('checkin', ['date' => '2026-01-02']); // '/checkin/2026-01-02'
route('checkin', ['date' => null]);         // '/checkin'
```

Pour `/a/?:x/?:y`, `['y' => 2]` sans `x` lève une `InvalidArgumentException` : le paramètre ne peut pas être utilisé sans les optionnels qui le précèdent. Pour `/a/?:x/b/?:y`, `['y' => 2]` donne `/a/b/2`.

Les paramètres fournis en excès (qui ne sont ceux d'aucune variante) sont ignorés. Quand un nom est partagé entre plusieurs verbes, la variante **GET** est préférée.

---

## Groupes de Routes

### Préfixe

```php
Route::group(function() {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/posts', [PostController::class, 'index']);
})->prefix('admin');

// URL générées :
// /admin/users
// /admin/posts
```

Une URI vide est valide dans un groupe préfixé : `Route::get('', 'index')` correspond au préfixe seul (`/admin`).

### Middlewares

```php
use App\Http\Middlewares\PreventRouteTampering;

Route::group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
})
->prefix('admin')
->middleware(PreventRouteTampering::class);

// Plusieurs middlewares : tableau ou séparateur |
Route::group(['middleware' => [PreventRouteTampering::class, LimitRate::class]], function() { /* ... */ });
Route::group(function() { /* ... */ })->middleware('auth|admin');
```

Le séparateur `|` peut être utilisé pour empiler les middlewares dans l'**ordre de déclaration** — `auth|admin` exécute d'abord `auth`, puis `admin`.

`middleware` et `prefix` se combinent. Un nom de middleware inconnu lève `MiddlewareNotFoundException` à la déclaration du groupe.

### Contraintes

```php
Route::group(['where' => ['id' => 'numeric', 'slug' => 'regexp|pattern:^[a-z]+$']], function() {
    Route::get('/:id', [ItemController::class, 'show']);
})->prefix('items');
```

L'option `where` associe à chaque paramètre la règle indiquée.

### Contrôleur par Défaut

```php
Route::controller(AdminController::class)->group(function() {
    Route::get('/users', 'index');
    Route::get('/users/:id', 'show');
    Route::post('/users', 'store');
})->prefix('admin');

// Équivalent à :
Route::get('/admin/users', [AdminController::class, 'index']);
Route::get('/admin/users/:id', [AdminController::class, 'show']);
Route::post('/admin/users', [AdminController::class, 'store']);
```

### Chaîner sur un groupe

Ce qui suit un `group()` dépend de la méthode :

| Méthode | Retourne le groupe ? |
|---|---|
| `prefix()`, `where()`, `whereNumber()`… (toutes les `whereX`), `pattern()`, `patterns()` | oui, chaînable |
| `middleware()`, `withoutMiddleware()`, `name()` | non, **terminales** |

### Groupes Imbriqués

```php
Route::group(function() {

    Route::controller(MemberController::class)->group(function() {
        // Actions réservées aux membres
    })->prefix('members');

    Route::controller(GuestController::class)->group(function() {
        // Actions accessibles aux invités
    })->prefix('guest');
})->middleware(LimitRate::class);

// Résultat :
// /members/download → LimitRate
// /guest/download   → LimitRate
```

Les middlewares d'un groupe parent sont **hérités** par les groupes imbriqués, qui peuvent librement y ajouter leurs propres middlewares sans écraser ceux du parent.

---

## Resource Controllers

### Définition

```php
Route::resource('users', UserController::class);

Route::resource('posts', PostController::class)
    ->only(['index', 'show']);
```

`Route::resource()` enregistre en une ligne les routes RESTful conventionnelles. C'est une boucle sur les mêmes `Route::get()`, `Route::post()`… que vous écririez à la main.

> **Pour les développeurs Laravel :** l'API est volontairement proche (`resource`, `apiResource`, `only`, `except`).

### Routes Générées

| Méthode | URL | Action |
|---------|-----|--------|
| GET | `/users` | index |
| GET | `/users/create` | create |
| POST | `/users` | store |
| GET | `/users/:user` | show |
| GET | `/users/:user/edit` | edit |
| PUT, PATCH | `/users/:user` | update |
| DELETE | `/users/:user` | destroy |

> **Nommage du paramètre :** le paramètre porte le nom de la ressource **au singulier** (`:user`), pas `:id`.

> **Les routes ne sont pas nommées automatiquement.** Nommez-les avec `names()` (voir [Options](#options-des-resources)).

### API Resource (sans create/edit)

```php
Route::apiResource('users', UserController::class);

// GET    /users       → index
// POST   /users       → store
// GET    /users/:user → show
// PUT, PATCH /users/:user → update
// DELETE /users/:user → destroy
```

`create` et `edit` retournent un formulaire HTML ; ils n'ont pas de sens côté API.

### Ressources Imbriquées

```php
Route::resource('users.posts', PostController::class);
```

| Action | Routes |
|---|---|
| index | `GET /users/posts`, `GET /users/:user/posts` |
| create | `GET /users/posts/create`, `GET /users/:user/posts/create` |
| store | `POST /users/posts`, `POST /users/:user/posts` |
| show | `GET /users/:user/posts/:post` |
| edit | `GET /users/:user/posts/:post/edit` |
| update | `PUT`, `PATCH /users/:user/posts/:post` |
| destroy | `DELETE /users/:user/posts/:post` |

`index`, `create` et `store` acceptent l'identifiant parent **de façon optionnelle** (voir [Paramètres Optionnels](#paramètres-optionnels)) : `/users/posts` désigne les posts sans parent précisé.

> Les ressources imbriquées se limitent à deux niveaux : `users.posts.comments` n'est pas pris en charge.

### Singleton Resource

```php
Route::singleton('profile', ProfileController::class);

// GET       /profile      → show
// GET       /profile/edit → edit
// PUT, PATCH /profile     → update

Route::apiSingleton('profile', ProfileController::class);

// GET       /profile → show
// PUT, PATCH /profile → update
```

Un *singleton* n'a ni `index`, ni `create`, ni `store`, ni `destroy`, et pas d'identifiant dans l'URI : il convient à une ressource dont il n'existe **qu'une instance par utilisateur** (profil, paramètres de compte). Il ne s'imbrique pas.

### Options des Resources

Toutes se chaînent après `resource()` / `apiResource()` :

| Méthode | Effet |
|---|---|
| `only($actions)` | ne garde que ces actions |
| `except($actions)` | retire ces actions |
| `names([...])` | nomme les routes : `['show' => 'users.show']` |
| `middleware($name)` | ajoute un middleware à toutes les routes |
| `middlewares($actions, $name)` | ajoute un middleware à certaines actions |
| `protect($actions, $preventTampering = true)` | protège les routes GET listées (voir [Prévention](#prévention-de-la-manipulation-de-route)) |
| `missing($callback)` | réponse de repli si le modèle n'est pas trouvé (`create`, `show`, `edit`) |
| `scoped([...])` | contrainte relationnelle entre paramètres (voir [Scope Binding](#scope-binding)) |
| `orderBy($order)` | tri par défaut de `index` |
| `limit($offset, $count)` | pagination fixe de `index` |
| `distinct(true)` | option `DISTINCT` pour `index` |
| `calcRows(true)` | calcul du nombre total de lignes pour `index` |
| `ignore()` | désactive la vérification d'unicité de `store` et `update` |
| `from($table)` | table ciblée par `destroy` |

```php
Route::resource('leaves', LeaveController::class)
    ->except(['destroy'])
    ->names([
        'index'  => 'leaves.home',
        'create' => 'leaves.create',
        'show'   => 'leaves.show',
    ])
    ->protect(['show']);

Route::resource('users', UserController::class)
    ->orderBy('country')
    ->limit(0, 10)
    ->missing(fn() => response()->notFound('Le modèle App\\Models\\User n\'existe pas'));
```

---

## Middlewares

### Application Globale

Chaque passerelle (`web`, `api`) déclare ses middlewares sous forme d'**alias** :

```php
// app/Http/Kernel.php
protected array $middleware = [
    'web' => [
        'auth' => \App\Http\Middlewares\AuthMiddleware::class,
        'session' => \App\Http\Middlewares\StartSession::class,
        'logger' => \App\Http\Middlewares\RequestLogger::class,
    ],
    'api' => [
        'rate_limit' => \App\Http\Middlewares\RateLimiter::class,
        'cors' => \App\Http\Middlewares\Cors::class,
        'auth' => \App\Http\Middlewares\JwtAuthMiddleware::class,
    ]
];
```

Un même alias (`auth`) peut pointer vers une implémentation différente selon la passerelle : `->middleware('auth')` fonctionne de façon uniforme. On peut aussi citer **directement le nom d'une classe** (`PreventRouteTampering::class`), sans alias.

Les middlewares **globaux** de la passerelle (`Web::getGlobals()`, `Api::getGlobals()`) s'ajoutent à toutes les routes de cette passerelle, sauf exclusion (voir ci-dessous).

### Application sur une Route

```php
// Middleware unique
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth');

// Plusieurs middlewares
Route::get('/admin', [AdminController::class, 'index'])->middleware(['auth', 'admin']);

// Middleware avec paramètres
Route::get('/api/users', [UserController::class, 'index'])->middleware('throttle:60,1');
```

### Application sur un Groupe

```php
Route::group(function() {
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/posts', [PostController::class, 'index']);
})->middleware('auth|admin');
```

### Exclusion de Middlewares

```php
// Sur une route
Route::get('/posts', [PostController::class, 'index'])->withoutMiddleware('rate_limit');

// Sur un groupe
Route::group(function() {
    Route::get('/users', [UserController::class, 'index']);
})->prefix('admin')->withoutMiddleware('rate_limit');
```

L'exclusion retire aussi les middlewares globaux de la passerelle.

### Middleware avec Paramètres

```php
use Clicalmani\Core\Http\Middlewares\Middleware;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Http\ResponseInterface;
use Clicalmani\Core\Http\RedirectInterface;

class ThrottleMiddleware extends Middleware
{
    public function __construct(
        private int $limit = 60,
        private int $window = 1
    ) {}

    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
    {
        // Logique de limitation
        return $next();
    }
}

Route::get('/users', [UserController::class, 'index'])->middleware('throttle:60,1');
```

Les arguments qui suivent `:` sont séparés par des virgules et passés, **sous forme de chaînes** et dans l'ordre, au constructeur : ici `$limit = 60` et `$window = 1`. Sans argument, le constructeur est appelé sans paramètre.


### Sous-middlewares

La syntaxe `nom@sous` (ou `nom[sous1,sous2]`) associe des sous-middlewares à un middleware que l'on enregistre avec `Route::middleware()` :

```php
Route::middleware('tokenizer@admin');
```

Les routes concernées reçoivent à la fois `tokenizer` et `admin`.

### Middleware qui Charge des Routes

Un middleware peut déclarer une méthode `boot()`. Lorsqu'on l'enregistre avec `Route::middleware('nom')`, le routeur appelle `boot()` et **toute route déclarée pendant cet appel reçoit automatiquement le middleware**. C'est le moyen de protéger tout un fichier de routes sans répéter `->middleware()` :

```php
// app/Http/Middlewares/Authenticator.php
class Authenticator extends Middleware
{
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
    {
        if ($user = $request->user()) {
            $user->authenticate();   // expiration glissante
            return $next();
        }

        return redirect()->route('home');
    }

    public function boot() : void
    {
        $this->include('auth');   // charge routes/auth.php
    }
}
```

```php
// routes/web.php — après les routes publiques
Route::middleware('tokenizer');   // 'tokenizer' → Authenticator : toutes les routes de routes/auth.php sont protégées
```

`Route::middleware('nom')` retourne un groupe : `Route::middleware('auth')->group(function() { … })` applique le middleware aux routes du callback.

### Pipeline d'Exécution

Un middleware n'est pas un simple hook "avant/après" : Tonka l'implémente comme un maillon d'un **pipeline** (pattern *Chain of Responsibility*), où chaque middleware décide explicitement d'appeler `$next($request)` pour poursuivre la chaîne — ou de la court-circuiter en retournant directement une réponse.

Concrètement, pour une route protégée par `auth|admin`, l'exécution suit cet ordre :

```
Requête entrante
   │
   ▼
StartSession ──▶ VerifyCsrfToken ──▶ AuthMiddleware ──▶ AdminMiddleware ──▶ Contrôleur
   │                                       │                    │
   └── (si la session est invalide, court-circuite ici et n'atteint jamais le contrôleur)
```

> **Pour les développeurs Symfony :** ce mécanisme est l'équivalent fonctionnel des *event listeners* sur `kernel.request` / `kernel.response`, mais exprimé sous une forme explicite de pipeline plutôt que d'événements découplés — plus proche, dans l'esprit, du `MiddlewareInterface` de PSR-15.

Chaque middleware peut donc intervenir **avant** l'appel à `$next()` (validation, journalisation d'entrée) **et/ou après** (modification de la réponse, journalisation de sortie) — c'est ce qui distingue un middleware d'un simple filtre : il enveloppe l'exécution du reste de la chaîne plutôt que de s'exécuter à un point fixe.

```php
public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
{
    $request->locale = 'en';

    if (!$request->hasHeader('X-Custom-Header')) {
        $response->status(422);
    }

    // ...

    return $next($request, $response);
}
```

---

## Route Model Binding

### Scope Binding

```php
Route::get('/users/:id/posts/:post_id', [PostController::class, 'show'])
    ->where('id', 'required|id|model:user')
    ->where('post_id', 'required|id|model:post')
    ->scoped(['post' => 'user_id']);

// Génère : /users/123/posts/456
// Vérifie que post.user_id = 123
```

`scoped()` ajoute une contrainte relationnelle **entre** deux paramètres bindés. Sans elle, `/users/123/posts/456` résoudrait `$post` dès qu'un post d'ID 456 existe, même s'il appartient à un autre utilisateur : c'est la garde contre un IDOR trivial.

> Pour une route exposant l'identifiant d'une ressource sensible, combinez `scoped()` avec la [prévention de la manipulation de route](#prévention-de-la-manipulation-de-route).

### Custom Binding

```php
// Dans le modèle
public function resolveRouteBinding($value, $field = null)
{
    return $this->where('slug', $value)->first();
}
```

### Resolution Callback

```php
// app/Providers/AppServiceProvider.php

public function boot(): void
{
    // Portée globale
    User::resolveRouteBindingUsing(function($value, $field = null) {
        return User::where('slug', $value)->first();
    });
}
```

---

## Builders Personnalisés

### Configuration

```php
// config/routing.php
return [
    'default_builder' => \Clicalmani\Routing\Factory\BasicBuilder::class,
    'builders' => [
        \App\Routing\LaravelStyleBuilder::class,
    ]
];
```

Les deux clés n'ont pas le même rôle :

- **`default_builder`** sert à **enregistrer** les routes : c'est lui qui interprète la syntaxe de vos définitions (`:id` ou `{id}`).
- **`builders`** est une liste de **repli pour le matching** : si le builder par défaut ne trouve pas de route, les builders listés sont essayés dans l'ordre.

Pour écrire vos routes en syntaxe `{id}`, le builder correspondant doit donc être le `default_builder`. Les builders implémentent le pattern **Strategy** : ils ne changent que la *syntaxe d'écriture*, le matching, les middlewares et le binding restent identiques en aval.

### Exemple : Builder Laravel Style

Un builder de « confort syntaxique » traduit la syntaxe puis **délègue** à `BasicBuilder` :

```php
<?php

namespace App\Routing;

use Clicalmani\Routing\Factory\BasicBuilder;
use Clicalmani\Routing\Route;

class LaravelStyleBuilder extends BasicBuilder
{
    private const PARAM_PATTERN = '/\{([a-zA-Z_][a-zA-Z0-9_]*)(\?)?\}/';

    public function create(string $uri) : Route
    {
        return parent::create($this->convert($uri));
    }

    // {id} → :id, {id?} → ?:id
    private function convert(string $uri) : string
    {
        return preg_replace_callback(
            self::PARAM_PATTERN,
            fn($m) => (isset($m[2]) ? '?' : '') . ':' . $m[1],
            $uri
        );
    }
}
```

Ne réimplémentez pas `matches()`, `locate()` ou `parameters()` : en hériter garantit que les corrections du matching profitent aussi à votre builder.

---

## Bonnes Pratiques

- **Nommez systématiquement vos routes** (`->name('users.show')`) et générez les URL avec `Route::resolve()` / `redirect()->route()`. Un paramètre oublié lève une exception au lieu de produire un lien mort.
- **Ne mélangez pas les responsabilités du contexte web et du contexte API** : un endpoint pour un client SPA/mobile stateless appartient à la passerelle `api`.
- **Préférez les Resource Controllers** dès qu'un contrôleur couvre le CRUD standard.
- **Évitez les routes de même forme sans validateurs** (`/items/:id` et `/items/:slug`) : la seconde ne sera jamais atteinte. Donnez-leur des validateurs distincts.
- **Limitez les paramètres optionnels** à un ou deux par route ; au-delà, déclarez des routes distinctes.
- **Une seule contrainte par paramètre** : la dernière remplace les précédentes. Composez-les avec `where('id', 'required|numeric|min:1')`.
- **Limitez `whereModel`** aux routes à faible trafic : elle exécute une requête par route candidate pendant le matching. Pour un fort volume, résolvez le modèle dans le contrôleur.
- **Utilisez `scoped()`** sur toute ressource imbriquée exposant un identifiant devinable, pour vous prémunir des IDOR.
- **Protégez les liens partagés hors session** (e-mails, exports, liens à usage unique) avec `PreventRouteTampering`.
- **Protégez un fichier de routes entier** avec un middleware à `boot()` plutôt qu'en répétant `->middleware()`.
- **Gardez les closures pour le prototypage** ; la logique métier appartient à un contrôleur, testable indépendamment du routeur.

---

## Prochaines Étapes

Maintenant que vous maîtrisez le routing :

1. **[Contrôleurs](controllers.md)** → Ajouter la logique métier
2. **[Middlewares](middleware.md)** → Protéger vos routes
3. **[Requêtes](requests.md)** → Valider les données
4. **[Vues](views.md)** → Créer l'interface utilisateur

---

**Routing maîtrisé !** 🚀 → [Contrôleurs](controllers.md)