# Autorisation

- [Autorisation](#autorisation)
- [Introduction](#introduction)
- [Politiques (Policies)](#politiques-policies-)
  - [Création d'une Politique](#creation-d-39-une-politique)
  - [Structure d'une Politique](#structure-d-39-une-politique)
  - [Enregistrement](#enregistrement)
  - [Utilisation](#utilisation)
- [Rôles et Permissions](#roles-et-permissions)
  - [Modèles de Base](#modeles-de-base)
  - [Attribution des Rôles](#attribution-des-roles)
  - [Vérification des Permissions](#verification-des-permissions)
- [Middleware d'Autorisation](#middleware-dautorisation)
- [Vérification dans les Contrôleurs](#verification-dans-les-controleurs)
  - [Méthode `can()`](#methode-code-can-code)
  - [Méthode `authorize()`](#méthode-code-authorize-code-)
- [Intégration avec Spark](#integration-avec-spark)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le système d'autorisation de Tonka permet de contrôler les actions qu'un utilisateur peut effectuer. Il repose sur deux piliers :

- **Politiques (Policies)** : Classes qui définissent les règles d'autorisation pour un modèle spécifique.
- **Rôles et Permissions** : Attribution de rôles aux utilisateurs, avec des permissions granulares.

---

## Politiques (Policies)

### Création d'une Politique

```bash
php tonka make:policy UserPolicy
```

### Structure d'une Politique

```php
namespace App\Policies;

use App\Models\User;
use Clicalmani\Core\Auth\Contract;

class UserPolicy extends Contract
{
    /**
     * Vérifie si un utilisateur peut voir un utilisateur
     */
    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->role === 'admin';
    }

    /**
     * Vérifie si un utilisateur peut créer un utilisateur
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Vérifie si un utilisateur peut mettre à jour un utilisateur
     */
    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->role === 'admin';
    }

    /**
     * Vérifie si un utilisateur peut supprimer un utilisateur
     */
    public function delete(User $user, User $target): bool
    {
        return $user->role === 'admin';
    }
}
```

### Enregistrement

Les politiques sont automatiquement découvertes dans le dossier `app/Policies/`.

### Utilisation

```php
// Dans un contrôleur
if ($request->user()->can('view', $user)) {
    // L'utilisateur peut voir
}

// Via la méthode authorize()
$this->authorize('update', $user);
```

---

## Rôles et Permissions

### Modèles de Base

```php
// Role Model
class Role extends Elegant
{
    protected $table = "roles r";
    protected $primaryKey = "r.id";
    
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_roles', 'role_id', 'user_id');
    }
}

// Permission Model
class Permission extends Elegant
{
    protected $table = "permissions p";
    protected $primaryKey = "p.id";
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}
```

### Attribution des Rôles

```php
// Attacher un rôle à un utilisateur
$user->roles()->attach(1);

// Attacher plusieurs rôles
$user->roles()->attach([1, 2, 3]);

// Synchroniser les rôles
$user->roles()->sync([1, 2, 5]);
```

### Vérification des Permissions

```php
// Via le modèle User
if ($user->hasRole('admin')) {
    // L'utilisateur est admin
}

if ($user->hasPermission('edit_users')) {
    // L'utilisateur peut éditer les utilisateurs
}

// Via la méthode can()
if ($user->can('edit_users')) {
    // L'utilisateur a la permission
}
```

---

## Middleware d'Autorisation

```php
// app/Http/Middlewares/AuthorizeMiddleware.php
namespace App\Http\Middlewares;

use Clicalmani\Core\Http\Middlewares\Middleware;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Http\ResponseInterface;

class AuthorizeMiddleware extends Middleware
{
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next, string $ability, ?string $model = null): ResponseInterface
    {
        $user = $request->user();
        
        if (!$user) {
            return $response->unauthorized();
        }
        
        if (!$user->can($ability, $model)) {
            return $response->forbidden();
        }
        
        return $next($request, $response);
    }
}
```

### Enregistrement

```php
// app/Http/Kernel.php
class Kernel extends HttpKernel
{
    protected array $middleware = [
        'web' => [
            'can' => \App\Http\Middlewares\AuthorizeMiddleware::class,
        ]
    ];
}
```

### Utilisation

```php
// Dans les routes
Route::get('/users/:id', [UserController::class, 'show'])
    ->middleware('can:view,user');

Route::post('/users', [UserController::class, 'store'])
    ->middleware('can:create,user');

Route::put('/users/:id', [UserController::class, 'update'])
    ->middleware('can:update,user');
```

---

## Vérification dans les Contrôleurs

### Méthode `can()`

```php
class UserController extends Controller
{
    public function show(Request $request, int $id)
    {
        $user = User::find($id);
        
        if (!$request->user()->can('view', $user)) {
            return response()->forbidden();
        }
        
        return Inertia::render('User/Show', ['user' => $user]);
    }
}
```

### Méthode `authorize()`

```php
class UserController extends Controller
{
    public function update(Request $request, int $id)
    {
        $user = User::find($id);
        
        $this->authorize('update', $user);
        
        // Si l'autorisation échoue, une exception est levée
        $user->update($request->validated());
        
        return redirect('/users');
    }
}
```

---

## Intégration avec Spark

Spark permet d'exposer les politiques au frontend.

```php
// config/spark.php
return [
    'only' => [
        [
            'name' => 'user.*',
            'policy' => \App\Policies\UserPolicy::class
        ]
    ]
];
```

**Frontend :**

```typescript
import { route } from 'tonka-router';

// Les routes sont automatiquement filtrées selon les politiques
const editUrl = route('user.edit', { id: 123 });
```

---

## Exemple Complet

### Politique

```php
namespace App\Policies;

use App\Models\User;
use Clicalmani\Core\Auth\Contract;

class PostPolicy extends Contract
{
    public function view(User $user, Post $post): bool
    {
        return $post->published || $user->id === $post->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === 'admin';
    }
}
```

### Contrôleur

```php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;

class PostController extends Controller
{
    public function show(Request $request, int $id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            abort(404);
        }
        
        if (!$request->user()->can('view', $post)) {
            abort(403);
        }
        
        return Inertia::render('Post/Show', ['post' => $post]);
    }

    public function update(PostRequest $request, int $id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            return response()->notFound();
        }
        
        $this->authorize('update', $post);
        
        $post->update($request->validated());
        
        return redirect('/posts/' . $id)
            ->with('success', 'Post updated!');
    }

    public function destroy(Request $request, int $id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            return response()->notFound();
        }
        
        if (!$request->user()->can('delete', $post)) {
            return response()->forbidden();
        }
        
        $post->delete();
        
        return redirect('/posts')
            ->with('success', 'Post deleted!');
    }
}
```

### Routes

```php
// routes/web.php
Route::get('/posts/:id', [PostController::class, 'show'])
    ->middleware('can:view,post');

Route::put('/posts/:id', [PostController::class, 'update'])
    ->middleware('can:update,post');

Route::delete('/posts/:id', [PostController::class, 'destroy'])
    ->middleware('can:delete,post');
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez l'autorisation :

1. **[CSRF Protection](csrf.md)** → Protéger vos formulaires
2. **[Chiffrement](encryption.md)** → Sécuriser les données
3. **[Événements](events.md)** → Système d'événements

---

**Autorisation comprise !** 🚀 → [CSRF Protection](csrf.md)