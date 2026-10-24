# Chiffrement

- [Chiffrement](#chiffrement)
- [Introduction](#introduction)
- [Chiffrement Symétrique](#chiffrement-symetrique)
  - [Encrypt](#encrypt)
  - [Decrypt](#decrypt)
- [Hash de Mots de Passe](#hash-de-mots-de-passe)
  - [password()](#password-)
  - [Vérification](#verification)
- [Configuration](#configuration)
  - [Hashing](#hashing)
  - [Paramètres de Hash](#parametres-de-hash)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Tonka fournit un système de chiffrement complet pour sécuriser vos données :

- **Chiffrement symétrique** : Chiffrement/déchiffrement de données avec OpenSSL
- **Hash de mots de passe** : Hash sécurisé avec Bcrypt ou Argon2
- **Hash de paramètres** : Vérification de l'intégrité des paramètres de requête

---

## Chiffrement Symétrique

### Encrypt

```php
use Clicalmani\Core\Auth\EncryptionServiceProvider;

// Via la classe
$encrypted = EncryptionServiceProvider::encrypt('Donnée sensible');

// Via le helper
$encrypted = encrypt('Donnée sensible');
```

### Decrypt

```php
use Clicalmani\Core\Auth\EncryptionServiceProvider;

// Via la classe
$decrypted = EncryptionServiceProvider::decrypt($encrypted);

// Via le helper
$decrypted = decrypt($encrypted);
```

**Exemple complet :**

```php
$data = 'Informations confidentielles';
$encrypted = encrypt($data);
$decrypted = decrypt($encrypted);

echo $decrypted; // "Informations confidentielles"
```

---

## Hash de Mots de Passe

### password()

```php
// Via la classe
$hash = EncryptionServiceProvider::password('monPassword123');

// Via le helper
$hash = password('monPassword123');
```

### Vérification

```php
// Vérification avec password_verify() native
$hash = password('monPassword123');

if (password_verify('monPassword123', $hash)) {
    echo 'Mot de passe correct';
}
```

### Configuration

```env
# .env
APP_KEY=base64:abcdefghijklmnopqrstuvwxyz1234567890=
```

```php
// config/hashing.php
return [

    /*
    |--------------------------------------------------------------------------
    | Default Pre-Hashing Method
    |--------------------------------------------------------------------------
    |
    | This option controls the default pre-hash method that will be used to hash
    | passwords for your application. By default, the sha1 algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | @see https://www.php.net/manual/en/function.hash-hmac-algos.php for supported
    | algorithms
    |
    */

    'algo' => 'sha256',

    'cipher' => 'AES-256-CBC',

    'iv_length' => 16, // encrypt method AES-256-CBC expects 16 bytes - otherwise you will get a warning

    /*
    |--------------------------------------------------------------------------
    | Default URL Hash Length
    |--------------------------------------------------------------------------
    |
    | URL hash may be used to prevent route tempering.
    |
    | This option controls the default length that will be used to
    | create URL hash. By default, 10 is used; however, you remain free to 
    | modify this option if you wish. You may specify 0 if you do not wish
    | to truncate the generated hash.
    |
    */

    'hash_length' => 10,

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'bcrypt' => [
        'cost' => env('BCRYPT_COST', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithm. These will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'argon' => [
        '2i' => true,
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
    ],

];
```

---

## Prevent Route Tampering

### Protéger une Route

```php
// routes/web.php
Route::get('/posts/:id', [PostController::class, 'edit'])
    ->protect();
```

### Vérification dans le Contrôleur

```php
// app/Http/Controllers/UserController.php

use Clicalmani\Core\Http\RequestInterface;
use \Clicalmani\Core\Resources\ViewInterface;
use App\Models\User;

// ...

public function show(RequestInterface $request) : ViewInterface
{
    return view('user.show');
}

public function edit(RequestInterface $request, int $id) : ViewInterface
{
    if (!$request->verifyParameters()) {
        return response()->unAuthorized()->with('message', 'Accès refusé !');
    }

    return view('user.edit', ['user' => User::find($id)]);
}
```

---

## Exemple Complet

### Chiffrement de Données

```php
namespace App\Http\Controllers;

use Clicalmani\Core\Auth\EncryptionServiceProvider;

class SecureController extends Controller
{
    public function store(Request $request)
    {
        // Chiffrement des données

        // Via le facade
        $encryptedData = \Clicalmani\Core\Support\Facade\Enc::encrypt($request->input('sensitive_data'));

        // Via helper
        $encryptedData = enc()->encrypt($request->input('sensitive_data'));
        
        // Stockage
        SecureData::create([
            'data' => $encryptedData,
            'user_id' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Data stored securely'
        ]);
    }

    public function show($id)
    {
        $secureData = SecureData::find($id);
        
        // Déchiffrement

        // Via facade
        $decryptedData = \Clicalmani\Core\Support\Facade\Enc::decrypt($secureData->data);

        // Via le helper
        $decryptedData = enc()->decrypt($secureData->data);
        
        return response()->json([
            'data' => $decryptedData
        ]);
    }
}
```

> `encrypt` et `decrypt` utiilse la variable d'environnement APP_KEY comme passphrase.

### Hash de Mots de Passe

```php
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        // Hash du mot de passe
        $validated['password'] = password($validated['password']);
        
        $user = User::create($validated);
        
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);
        
        $user = auth()->user();
        
        // Vérification du mot de passe actuel
        if (!password_verify($validated['current_password'], $user->password)) {
            return response()->error('Current password is incorrect');
        }
        
        // Mise à jour du mot de passe
        $user->update([
            'password' => password($validated['new_password'])
        ]);
        
        return response()->success('Password updated successfully');
    }
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez le chiffrement :

1. **[Événements](events.md)** → Système d'événements
2. **[Messenger](messenger.md)** → Queues asynchrones
3. **[Broadcasting](broadcasting.md)** → Communication temps réel

---

**Chiffrement compris !** 🚀 → [Événements](events.md)