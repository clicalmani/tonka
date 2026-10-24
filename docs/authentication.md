# Authentification

- [Authentification](#authentification)
- [Introduction](#introduction)
- [Configuration](#configuration)
  - [Fichier .env](#fichier-env)
  - [Fichier de Configuration](#fichier-de-configuration)
- [JWT (JSON Web Token)](#jwt-json-web-token-)
  - [Génération d'un Token](#generation-d-39-un-token)
  - [Vérification d'un Token](#verification-d-39-un-token)
- [Middleware d'Authentification](#middleware-d-39-authentification)
- [Contrôleur d'Authentification](#controleur-d-39-authentification)
  - [Fournisseur d'Authentification (AuthServiceProvider)](#fournisseur-d-39-authentification-authserviceprovider-)
  - [Connexion](#connexion)
  - [Inscription](#inscription)
  - [Déconnexion](#deconnexion)
  - [Profil](#profil)
- [Helpers](#helpers)
  - [auth()](#auth-)
  - [token()](#token-)
  - [verify_token()](#verify_token-)
  - [bearerToken()](#bearertoken-)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Tonka utilise **JWT (JSON Web Tokens)** pour l'authentification. Ce système permet une authentification sans état (stateless), particulièrement adaptée aux API et aux applications modernes découplant front-end et back-end.

**Caractéristiques :**
- ✅ Tokens JWT sécurisés
- ✅ Expiration configurable
- ✅ Authentification sans état
- ✅ Support natif des middlewares
- ✅ Intégration native avec DriftQL

> **Pour les développeurs Laravel/Symfony :** cette approche se rapproche de Laravel Sanctum (jetons API légers) plutôt que de Passport (OAuth2 complet), et de `lexik/jwt-authentication-bundle` côté Symfony. La particularité de Tonka est de coupler le jeton JWT à une trace de session persistée en base (`auth_access`, voir [Fournisseur d'Authentification](#fournisseur-dauthentification-authserviceprovider)), ce qui permet d'invalider un jeton côté serveur — un JWT pur, classiquement stateless, ne le permet pas nativement.

---

## Configuration

### Fichier .env

```env
APP_KEY=base64:abcdefghijklmnopqrstuvwxyz1234567890=
```

```bash
php tonka key:generate
```

`APP_KEY` sert de secret de signature pour les jetons JWT émis par l'application — sa régénération invalide immédiatement tous les jetons précédemment émis. Ne le committez jamais dans le contrôle de version, et utilisez une valeur distincte par environnement (développement, staging, production).

### Fichier de Configuration

```php
// config/auth.php
/*
|--------------------------------------------------------------------------
| Application Authentication Configuration
|--------------------------------------------------------------------------
|
| Here you may specify the configuration for the authentication services
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration for the JWT tokens
    |
    */
    'tokens' => [

        /*
        |--------------------------------------------------------------------------
        | JWT Header Configuration
        |--------------------------------------------------------------------------
        |
        | Here you may specify the configuration for the JWT header
        |
        */
        'header' => [
            'algo' => 'HS256',
            'type' => 'JWT'
        ],

        /*
        |--------------------------------------------------------------------------
        | JWT Payload Configuration
        |--------------------------------------------------------------------------
        |
        | Here you may specify the configuration for the JWT payload
        |
        */
        'expire' => 86400,
        'algo' => 'sha256'
    ]
];
```

`tokens.header.algo` (`HS256`) est l'algorithme déclaré dans l'en-tête du jeton, au sens de la spécification JWT ; `tokens.algo` (`sha256`) est l'algorithme de hachage sous-jacent effectivement utilisé pour calculer la signature HMAC — les deux doivent rester cohérents entre eux (`HS256` = HMAC-SHA256) si vous modifiez l'un des deux.

---

## JWT (JSON Web Token)

### Génération d'un Token

```php
// Via le helper
$token = token($user->id, 3600); // Expire dans 1 heure
```

### Vérification d'un Token

```php
// Via le helper
$payload = verify_token($token);
```

`verify_token()` retourne le payload décodé si le jeton est valide (signature correcte et non expiré), ou une valeur falsy dans le cas contraire — vérifiez toujours son retour avant d'exploiter le payload.

---

## Middleware d'Authentification

### Définition

```php
// app/Http/Middlewares/Authenticator.php
namespace App\Http\Middlewares;

use Clicalmani\Core\Http\Middlewares\Middleware;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Http\ResponseInterface;

class Authenticator extends Middleware 
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Core\Http\RequestInterface $request Request object
     * @param \Clicalmani\Core\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Core\Http\ResponseInterface|\Clicalmani\Core\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : \Clicalmani\Core\Http\ResponseInterface|\Clicalmani\Core\Http\RedirectInterface
    {
        if ($user = $request->user()) {
            if ($user->isAuthenticated() && false === $user->isOnline()) {
                $user->destroy();
                return redirect()->route('home');
            }

            $user->authenticate(); // Prolongation glissante de la session (sliding expiration)

            return $next();
        }

        return redirect()->route('home');
    }

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot() : void
    {
        $this->include('auth');
    }
}
```

Le renouvellement automatique de l'authentification à chaque requête valide (`$user->authenticate()`) implémente une expiration glissante : tant que l'utilisateur reste actif, sa session ne bascule jamais en expiration par inactivité.

### Enregistrement

```php
// app/Http/Kernel.php
class Kernel extends HttpKernel
{
    protected array $middleware = [
        'web' => [
            'auth' => \App\Http\Middlewares\Authenticator::class,
        ],
        'api' => [
            'auth' => \App\Http\Middlewares\Authenticator::class,
        ]
    ];
}
```

---

## Contrôleur d'Authentification

### Fournisseur d'Authentification (AuthServiceProvider)

Le cœur du mécanisme d'authentification n'est pas porté par un contrôleur, mais par un Service Provider dédié, qui implémente la persistance de l'état connecté et devient accessible partout dans l'application via le helper `auth()`.

```php
namespace App\Providers;

use App\Models\AuthAccess;
use Clicalmani\Core\Auth\Authenticate;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Support\Facades\DB;

class AuthServiceProvider extends Authenticate
{
	protected string $userModel = \App\Models\User::class;

	public function getConnectedUserID(?RequestInterface $request) : mixed
	{
		if ($user_id = $request->session('user:id')->get()) {
			return (int)$user_id;
		}
		
		return null;
	}

    /**
	 * Authentifie l'utilisateur, ou renouvelle son authentification.
	 * 
	 * @return void
	 */
	public function authenticate() : void
	{
		if ($this->user_id) {
			DB::table('auth_access')
				->insert([
					['user_id' => $this->user_id, 'token' => token($this->user_id)]
				], true)
				->where('user_id = :user', ['user' => (int) $this->user_id])
				->exec();
		}
	}

	/**
	 * Indique si l'utilisateur possède une trace d'authentification.
	 * 
	 * @return bool
	 */
	public function isAuthenticated() : bool
	{
		return !!DB::table('auth_access')->where('user_id = :user_id', ['user_id' => $this->user_id])->get('token')->first();
	}

	/**
	 * Indique si l'utilisateur est actuellement en ligne (jeton valide et non expiré).
	 * 
	 * @return bool
	 */
	public function isOnline() : bool
	{
		$auth = DB::table('auth_access')->where('user_id = :user_id', 'AND', ['user_id' => $this->user_id])->get('token')->first();
		if ($auth && verify_token((string)$auth->token)) return true;
		return false;
	}

	public function destroy()
	{
		if ($this->user_id) {
			AuthAccess::where('user_id = ?', [$this->user_id])->first()?->delete();
		}
	}

    public function user() : \App\Models\User
    {
        return $this->user;
    }

    public function id() : ?int
    {
        return $this->user->id;
    }
}
```

`AuthServiceProvider` est accessible via le helper `auth()`, et les attributs du modèle `User` associé sont exposés directement dessus — par exemple `auth()->id()`, `auth()->first_name`.

> **Distinction entre `isAuthenticated()` et `isOnline()` :** la première vérifie qu'une trace d'authentification existe pour l'utilisateur (une ligne dans `auth_access`) ; la seconde vérifie en plus que le jeton associé est **toujours valide** (signature correcte, non expiré). Le middleware [`Authenticator`](#middleware-dauthentification) s'appuie sur cette distinction pour détecter une session dont la trace existe encore mais dont le jeton a expiré, et la purger explicitement (`$user->destroy()`) plutôt que de la laisser dans un état incohérent.

### Connexion

```php
// app/Http/Controllers/AuthController.php

use Clicalmani\Core\Http\RequestInterface;

// ..

public function login(RequestInterface $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

      $user = User::where('email = ?', [$validated['email']])->first();

    if (!$user /* || vérification du mot de passe à confirmer */) {
        return response()->status(401)->json(['error' => 'Invalid credentials']);
    }

    $request->session()->set('user:id', $user->id);
    auth()->authenticate();

    return response()->json([
        'success' => true,
        'token' => token($user->id, 3600),
        'user' => $user
    ]);
 }
```

### Inscription

```php
// app/Http/Controllers/UserController.php

public function store(RequestInterface $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|password|min:8|common:no'
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => password($validated['password'])
    ]);

    $token = token($user->id, 3600);

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => $user
    ]);
}
```

`password()` est le helper de hachage (voir [Validation des Entités](encryption.md)).

### Déconnexion

```php
public function logout(RequestInterface $request)
{
    // Le jeton est invalidé côté serveur via auth()->destroy() (suppression
    // de la trace en base) ; côté client, le jeton doit également être
    // supprimé du stockage local (voir Utilisation Frontend ci-dessous).

    auth()->destroy();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}
```

### Profil

```php
// app/Http/Controllers/AuthController.php

// ...

public function profile(RequestInterface $request)
{
    $user = $request->user(); // Instance de AuthServiceProvider pour l'utilisateur connecté

    return response()->json([
        'success' => true,
        'user' => $user
    ]);
}
```

---

## Helpers

### auth()

```php
// Récupération du fournisseur d'authentification (utilisateur courant)
$user = auth();

// Accès aux propriétés
$id = auth()->id();
$name = auth()->name;
$email = auth()->email;
```

### token()

```php
// Génération d'un jeton, valide 1 heure (3600 secondes)
$token = token($user->id, 3600);
```

### verify_token()

```php
// Vérification d'un jeton
$payload = verify_token($token);

// Accès au payload décodé
$userId = $payload->jti;
$exp = $payload->exp;
```

### bearerToken()

```php
// Récupération du jeton depuis l'en-tête Authorization: Bearer ...
$token = $request->bearerToken();
```

---

## Exemple Complet

### Routes

```php
// routes/auth.php — chargé automatiquement par Authenticator::boot()
// via $this->include('auth')
use App\Http\Controllers\AuthController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth');
Route::get('/auth/profile', [AuthController::class, 'profile'])
    ->middleware('auth');
```

```php
// routes/api.php
// Attache le middleware 'auth' à l'ensemble des routes définies dans
// routes/auth.php, incluses automatiquement par le middleware lui-même.
Route::middleware('auth');
```

### Utilisation Frontend

```typescript
// login.ts
const login = async (email: string, password: string) => {
    const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    
    if (data.success) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
    }
    
    return data;
};

// Requête authentifiée
const fetchProfile = async () => {
    const token = localStorage.getItem('token');
    
    const response = await fetch('/api/auth/profile', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    
    return response.json();
};
```

!> 💣 **Point d'attention sécurité :** stocker un jeton dans `localStorage` l'expose à tout script s'exécutant sur la page (vulnérabilité XSS). Pour une application à fort enjeu de sécurité, préférez un cookie `httpOnly` + `secure` (voir [Cookies](sessions.md#cookies-1)) à `localStorage`, qui reste inaccessible depuis JavaScript même en cas de faille XSS.

---

## Prochaines Étapes

Maintenant que vous maîtrisez l'authentification :

1. **[Autorisation](authorization.md)** → Gérer les permissions
2. **[Chiffrement](encryption.md)** → Sécuriser les données
3. **[CSRF](csrf.md)** → Protection CSRF

---

**Authentification comprise !** 🚀 → [Autorisation](authorization.md)