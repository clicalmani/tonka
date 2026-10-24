# Contrôleurs

Les contrôleurs orchestrent le traitement d'une requête HTTP validée par le routeur : ils font le lien entre la couche métier (modèles, services) et la réponse renvoyée au client. Cette page couvre leur création, leur structuration et les patterns courants — avec, là encore, quelques repères pour les développeurs venant de Laravel ou de Symfony.

- [Contrôleurs](#controleurs)
- [Création d'un Contrôleur](#creation-d-39-un-controleur)
- [Structure d'un Contrôleur](#structure-d-39-un-controleur)
- [Actions](#actions)
  - [Méthodes Standards](#methodes-standards)
  - [Actions Invokable](#actions-invokable)
- [Injection de Dépendances](#injection-de-dependances)
  - [Dans le Constructeur](#dans-le-constructeur)
  - [Dans les Méthodes](#dans-les-methodes)
  - [Injection de Services](#injection-de-services)
- [Middlewares de Contrôleur](#middlewares-de-controleur)
- [Réponses](#reponses)
  - [Vues](#vues)
  - [JSON](#json)
  - [Redirections](#redirections)
  - [Fichiers](#fichiers)
- [Validation](#validation)
  - [Form Requests](#form-requests)
  - [Validation Directe](#validation-directe)
  - [Validation avec Attributs PHP 8](#validation-avec-attributs-php-8)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Création d'un Contrôleur

### Commande de Génération

```bash
# Contrôleur simple
php tonka make:controller UserController

# Contrôleur ressource (CRUD complet)
php tonka make:controller UserController --resource

# Contrôleur API
php tonka make:controller ApiUserController --api

# Contrôleur invokable
php tonka make:controller UserController --invokable

# Contrôleur ressource avec modèle (active le route model binding)
php tonka make:controller UserController --resource --model=User

# Contrôleur avec Form Requests générées automatiquement
php tonka make:controller UserController --resource --model=User --requests
```

> **Pour les développeurs Laravel :** ces options (`--resource`, `--api`, `--invokable`, `--model`, `--requests`) reprennent presque terme à terme celles d'`artisan make:controller`.

### Structure Générée

```php
namespace App\Http\Controllers;

use Clicalmani\Core\Http\Controller;
use Clicalmani\Core\Http\RequestInterface;

class UserController extends Controller
{
    public function index()
    {
        // Logique ici
    }

    // ...
}
```

---

## Structure d'un Contrôleur

### Contrôleur de Base

```php
namespace App\Http\Controllers;

use Clicalmani\Core\Http\Controller;
use Clicalmani\Core\Http\RequestInterface;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $users = User::all();
        return Inertia::render('User/Index', ['users' => $users]);
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return Inertia::render('User/Create');
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function store(UserRequest $request)
    {
        $user = User::create($request->validated());
        return redirect('/users');
    }

    /**
     * Affiche un utilisateur
     */
    public function show(int $id)
    {
        $user = User::find($id);
        return Inertia::render('User/Show', ['user' => $user]);
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(int $id)
    {
        $user = User::find($id);
        return Inertia::render('User/Edit', ['user' => $user]);
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(UserRequest $request, int $id)
    {
        $user = User::find($id);
        $user->update($request->validated());
        return redirect('/users');
    }

    /**
     * Supprime un utilisateur
     */
    public function destroy(int $id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect('/users');
    }
}
```

Cette structure correspond aux sept actions RESTful conventionnelles générées par `Route::resource()` (voir [Resource Controllers](routing.md#resource-controllers)) — chaque méthode porte le nom de l'action qu'elle implémente, sans surprise pour quiconque a déjà lu la page consacrée au routing.

---

## Actions

### Méthodes Standards

```php
class UserController extends Controller
{
    // GET /users
    public function index() { /* Liste */ }

    // GET /users/create
    public function create() { /* Formulaire */ }

    // POST /users
    public function store(Request $request) { /* Création */ }

    // GET /users/:id
    public function show($id) { /* Détail */ }

    // GET /users/:id/edit
    public function edit($id) { /* Formulaire */ }

    // PUT /users/:id
    public function update(Request $request, $id) { /* Mise à jour */ }

    // DELETE /users/:id
    public function destroy($id) { /* Suppression */ }
}
```

### Actions Invokable

```php
class ProfileController extends Controller
{
    public function __invoke()
    {
        return view('profile');
    }
}
```

**Route associée :**
```php
Route::get('/', MonController::class);
```

Un contrôleur invokable convient aux endpoints portant une seule responsabilité clairement identifiable (`ProfileController` n'affiche que le profil, par exemple) — il évite d'imposer un nom de méthode arbitraire (`index`, `handle`...) là où une seule action a de sens.

---

## Injection de Dépendances

Le conteneur de services (voir [Architecture](architecture.md#le-conteneur-de-services)) résout automatiquement les dépendances typées d'un contrôleur, qu'elles soient déclarées dans le constructeur ou directement dans la signature d'une méthode d'action.

### Dans le Constructeur

```php
class UserController extends Controller
{
    public function __construct(
        private UserRepository $repository,
        private LoggerInterface $logger
    ) {}

    public function index()
    {
        $users = $this->repository->all();
        $this->logger->info('Users listed');
        return view('users.index', compact('users'));
    }
}
```

> **Pour les développeurs Symfony :** cette résolution automatique par typage correspond à l'*autowiring* du conteneur Symfony DI — aucune configuration explicite n'est nécessaire tant que la dépendance est correctement typée et enregistrée dans le conteneur.

### Dans les Méthodes

```php
class UserController extends Controller
{
    public function store(
        UserRequest $request,
        MailerInterface $mailer,
        NotificationService $notifier
    ) {
        $user = User::create($request->validated());
        $mailer->send(new WelcomeEmail($user));
        $notifier->notify($user);
        return redirect('/users');
    }
}
```

L'injection *par méthode* est préférable à l'injection par constructeur lorsque la dépendance n'est utilisée que par une seule action — elle évite d'alourdir le constructeur de dépendances inutiles pour les autres méthodes du contrôleur.

Pour rendre un service personnalisé **injectable par méthode**, celui-ci doit implémenter l'interface `InjectionResolverInterface` (Voir [Service Providers](providers.md) pour plus de détails) :

```php
use Clicalmani\Core\Providers\ServiceProviderInterface;
use Clicalmani\Core\Http\Controllers\InjectionResolverInterface;
use Clicalmani\Core\Http\Controllers\ReflectorInterface;
use Clicalmani\Routing\Route;

class MonService implements ServiceProviderInterface, InjectionResolverInterface
{
    public function register(): void
    {
        // ...
    }

    public function boot(): void
    {
        // ...
    }

    /**
     * Tente de résoudre le paramètre à la position donnée.
     * 
     * @return bool True si ce resolver a pris en charge ce paramètre.
     */
    public function resolve(
        \ReflectionParameter $parameter,
        ReflectorInterface $reflector,
        Route $route,
        array &$args,
        int $position
    ): bool {
        // ...
    }
}
```

Voici un exemple de résolution de service :

```php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProviderInterface;
use Clicalmani\Core\Http\Controllers\InjectionResolverInterface;
use Clicalmani\Core\Http\Controllers\ReflectorInterface;
use Clicalmani\Core\Acme\Container;
use Clicalmani\Routing\Route;
use App\Services\PaymentService;

class PaymentServiceProvider implements ServiceProviderInterface, InjectionResolverInterfac
{
    public function register(): void
    {
        app()->addService(
            'payment',                             
            PaymentService::class,                 
            function($config) {                    
                $config->args([
                    app()->dependency('param', 'payment.api_key'),
                    app()->dependency('param', 'payment.secret')
                ]);
            }
        );

        app()->config->set('payment', require_once config_path('/payment.php'));
    }

    public function boot(Container $container): void
    {
        $payment = $container->get('payment');
        $payment->setMode(config('payment.mode'));
    }

    public function resolve(
        \ReflectionParameter $parameter,
        ReflectorInterface $reflector,
        Route $route,
        array &$args,
        int $position
    ): bool {
        $class = $parameter->getType()->getName();

        try {
            if ($class !== Payment::class) {
                return false;
            }

            $args[$position] = container()->get('payment');

            return true;
        } catch (\Throwable $e) {
            // Laisse le resolver suivant tenter sa chance
            return false;
        }
    }
}
```

!> Optez pour l'injection par méthode que lorsque le besoin se fait réelement sentir, dans le cas contraire l'injection par le contrôleur est suffisant étant donnée que Tonka le supporte par défaut.

### Injection de Services

```php
// Les services sont automatiquement injectés
class UserController extends Controller
{
    public function show(User $user, Request $request)
    {
        // $user est résolu par le Route Model Binding
        // $request est injecté automatiquement
        return view('users.show', compact('user'));
    }
}
```

---

## Middlewares de Contrôleur

```php
use Clicalmani\Core\Traits\HasMiddleware;

class UserController extends Controller
{
    use HasMiddleware;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['index', 'create', 'show']);
        $this->middleware('guest')->only(['login', 'register']);
    }

    public function index() { /* auth appliqué */ }
    public function store() { /* auth + admin appliqués */ }
    public function login() { /* guest appliqué */ }
}
```

`only()` et `except()` sont mutuellement exclusifs par appel à `middleware()` : chacun filtre les actions concernées par **ce** middleware précisément, sans affecter les autres appels — vous pouvez ainsi combiner plusieurs middlewares avec des périmètres d'action différents dans un même constructeur.

---

## Réponses

### Vues

```php
// Inertia (React/Vue)
return Inertia::render('User/Index', ['users' => $users]);

// Twig
return view('users.index', ['users' => $users]);
```

### JSON

```php
return response()->json([
    'success' => true,
    'data' => $users
]);

// Réponse de succès normalisée
return response()->success(['user' => $user]);

// Réponse d'erreur normalisée
return response()->error('User not found');
```

`success()` et `error()` encapsulent une structure de réponse JSON cohérente (code de statut, enveloppe `success`/`data` ou `error`) sans avoir à la reconstruire manuellement à chaque endpoint — utile pour garantir un contrat homogène côté client API.

### Redirections

```php
// Redirection simple
return redirect('/users');

// Redirection avec message flash
return redirect('/users')->with('success', 'User created!');

// Redirection avec données
return redirect('/users')->withInput($request->all());

// Redirection avec erreur
return redirect('/users')->withError('User not found');

// Redirection vers une route nommée
return redirect()->route('users.show', ['id' => $user->id]);

// Retour à la page précédente
return back();

// Redirection côté client (Inertia)
return Inertia::location('/login');
```

> **Point d'attention :** `Inertia::location()` déclenche une navigation **complète** du navigateur (rechargement de page), à la différence d'une redirection Inertia classique qui reste dans le cycle de vie SPA. Réservez-la aux cas où une sortie du contexte Inertia est réellement nécessaire (ex. redirection vers une URL externe ou un provider d'authentification).

### Fichiers

```php
// Téléchargement
return response()->sendFile('/path/to/file.pdf', 'document.pdf');

// Streaming
return response()->stream('/path/to/video.mp4');

// Vue
return response()->view('users.index', ['users' => $users]);
```

---

## Validation

### Form Requests

```php
// app/Http/Requests/UserRequest.php
class UserRequest extends Request
{
    public function authorize(): bool
    {
        return auth()->user()->can('create_user');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->email),
        ]);
    }

    public function signatures(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8|confirmed',
            'age' => 'nullable|integer|min:18',
            'role' => 'required|in:admin,user,moderator'
        ];
    }
}
```

> **Pour les développeurs Laravel :** le triptyque `authorize()` / `prepareForValidation()` / règles de validation reproduit fidèlement le fonctionnement des Form Requests Laravel — seul le nom de la méthode portant les règles diffère (`signatures()` plutôt que `rules()`).

### Validation Directe

```php
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create($validated);
        return redirect('/users');
    }
}
```

La validation directe convient aux actions simples ne justifiant pas une classe Form Request dédiée ; dès que les règles sont réutilisées entre plusieurs actions, ou que la logique d'autorisation devient non triviale, préférez une Form Request.

### Validation avec Attributs PHP 8

```php
use Clicalmani\Validation\AsValidator;

class UserController extends Controller
{
    #[AsValidator([
        'email' => 'required|email|unique:users',
        'password' => 'required|password|min:8'
    ])]
    public function store(Request $request)
    {
        $user = new User;
        $user->swap(); // "swap" utilise les champs validés pour peupler et créer l'utilisateur
        $user->save();
        return response()->json($user);
    }
}
```

> **Pour les développeurs Symfony :** cette approche déclarative par attribut PHP 8 (`#[AsValidator]`) est dans le même esprit que les contraintes `#[Assert\...]` de Symfony Validator — la règle de validation vit au plus près du code qu'elle contraint, plutôt que dans une classe séparée.

---

## Exemple Complet

```php
namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Support\Facades\DB;
use Clicalmani\Core\Traits\HasMiddleware;

class UserController extends Controller
{
    use HasMiddleware;

    public function __construct()
    {
        $this->middleware('auth')->only(['index', 'show']);
        $this->middleware('admin')->except(['index', 'show']);
    }

    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $users = User::where('active = ?', [1])
                    ->orderBy('created_at DESC')
                    ->limit($request->input('offset', 1), 10)
                    ->get();

        return Inertia::render('User/Index', ['users' => $users]);
    }

    /**
     * Création d'un utilisateur
     */
    public function store(UserRequest $request)
    {
        $user = DB::transaction(function() use ($request) {
            $user = User::create($request->validated());
            if ($user) {
                Profile::create([
                    'bio' => $request->input('bio'),
                ]);
                return $user;
            }
            return false;
        });

        if ($user) {
            return redirect('/users')
                ->with('success', 'User created successfully!');
        }

        return redirect('/users')
                    ->with('error', 'Failed to create user');
    }

    /**
     * Détail d'un utilisateur
     */
    public function show(int $id)
    {
        $user = User::with(['profile', 'posts'])->find($id);

        if (!$user) {
            abort(404);
        }

        return Inertia::render('User/Show', ['user' => $user]);
    }

    /**
     * Mise à jour d'un utilisateur
     */
    public function update(UserRequest $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->notFound();
        }

        $user->update($request->validated());

        return redirect('/users/' . $id)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Suppression d'un utilisateur
     */
    public function destroy(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->notFound();
        }

        $user->delete();

        return redirect('/users')
            ->with('success', 'User deleted successfully!');
    }
}
```

Cet exemple illustre plusieurs bonnes pratiques réunies : middlewares scopés par action (`only`/`except`), transaction explicite (`DB::transaction`) pour garantir l'atomicité de la création d'un utilisateur et de son profil associé, et vérification systématique de l'existence de la ressource avant toute opération de mutation ou de suppression.

---

## Prochaines Étapes

Maintenant que vous maîtrisez les contrôleurs :

1. **[Requêtes](requests.md)** → Valider les données entrantes
2. **[Middlewares](middleware.md)** → Protéger vos contrôleurs
3. **[Réponses](responses.md)** → Retourner différentes réponses
4. **[Base de Données](database.md)** → Travailler avec les modèles

---

**Contrôleurs compris !** 🚀 → [Requêtes](requests.md)