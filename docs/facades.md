# Facades

- [Facades](#facades)
- [Introduction](#introduction-17)
- [Facades Principales](#facades-principales)
  - [Route](#route)
  - [Request](#request)
  - [DB](#db)
  - [Cache](#cache)
  - [Log](#log)
  - [Config](#config)
  - [Storage](#storage)
  - [Mail](#mail)
- [Facades Disponibles](#facades-disponibles)
- [Comment ça fonctionne ?](#comment-ça-fonctionne-)
- [Utilisation](#utilisation-3)
- [Création d'une Facade](#création-dune-facade)
- [Prochaines Étapes](#prochaines-étapes-13)

---

## Introduction

Les **Facades** de Tonka fournissent une interface statique et expressive aux services enregistrés dans le conteneur d'injection de dépendances (`ServiceContainer`). Elles agissent comme des "proxies statiques" vers les instances sous-jacentes des services.

Grâce aux facades, vous bénéficiez de la syntaxe concise d'appels statiques sans sacrifier la testabilité ni la flexibilité du code orienté objet.

```php
// Sans facade (résolution manuelle via conteneur)
$router = container()->get('router');
$router->get('/users', [UserController::class, 'index']);

// Avec la facade Route
use Clicalmani\Core\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);

```

---

## Facades Principales

### Route

La facade `Route` est le point d'entrée pour déclarer et organiser les routes HTTP de votre application. Elle délègue ses appels au routeur du framework (`\Clicalmani\Routing\Router`).

```php
use Clicalmani\Core\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\DashboardController;

// 1. Enregistrement des verbes HTTP basiques
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// 2. Nommage de route (pour génération d'URL ultérieure)
Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');

// 3. Groupement avec préfixe, middleware et namespace
Route::group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/settings', [DashboardController::class, 'settings']);
})->prefix('admin')->middleware(['auth', 'admin']);

```

---

### Request

La facade `Request` permet d'inspecter et d'extraire la totalité de la requête HTTP courante (`Clicalmani\Core\Http\Request`). Elle facilite la récupération des paramètres de formulaire, de la chaîne de requête, des en-têtes et des fichiers téléversés.

```php
use Clicalmani\Core\Support\Facades\Request;

// 1. Récupération de données d'entrées
$name = Request::input('name'); // Récupère une valeur (POST/GET)
$email = Request::input('email', 'contact@example.com'); // Valeur par défaut
$all = Request::all(); // Tous les champs sous forme de tableau

// 2. Filtrage d'entrées
$credentials = Request::only(['email', 'password']);
$userData = Request::except(['_token', 'password_confirmation']);

// 3. Inspection d'en-têtes et d'état
$bearerToken = Request::bearerToken();
$clientIp = Request::ip();

if (Request::isMethod('POST') && Request::wantsJson()) {
    // Requête AJAX / JSON POST
}

// 4. Gestion des fichiers téléversés
if (Request::hasFile('avatar')) {
    $file = Request::file('avatar');
    $path = $file->store('avatars', 'public');
    $path = $file->moveTo('public', 'avatars');
}

```

---

### DB

La facade `DB` encapsule le gestionnaire de base de données (`Clicalmani\Database\DB`) et permet de composer des requêtes SQL dynamiques via un Query Builder fluide, de gérer les transactions et d'exécuter des requêtes brutes.

```php
use Clicalmani\Core\Support\Facades\DB;

// 1. Requêtes fluides via Query Builder
$activeUsers = DB::table('users')
    ->select(['id', 'name', 'email'])
    ->where('status = ?', ['active'])
    ->orderBy('created_at', 'DESC')
    ->get();

$singleUser = DB::table('users')->where('id = ?', [10])->first();

// 2. Insertions, Mises à jour, Suppressions
DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => now()
])->query();

DB::table('users')
    ->where('id = ?', [10])
    ->update(['name' => 'Jane Doe']);

DB::table('users')->where('id = ?', [10])->delete();

// 3. Transactions sécurisées avec rollback automatique en cas d'exception
DB::transaction(function() {
    DB::table('accounts')->where('id = ?', [1])->decrement('balance', 100);
    DB::table('accounts')->where('id = ?', [2])->increment('balance', 100);
});

// 4. Agrégations
$totalOrders = DB::table('orders')->count();
$revenue = DB::table('orders')->where('status = ?', ['completed'])->sum('amount');

```

---

### Cache

La facade `Cache` fournit un accès direct au sous-système de mise en cache. Elle permet d'accélérer l'application en évitant des recalculs ou des requêtes BDD répétitives.

```php
use Clicalmani\Core\Support\Facades\Cache;

// 1. Stockage de données (durée de vie en secondes)
Cache::set('stats.daily_users', 1450, 3600); // Valide 1 heure

// 2. Récupération avec valeur par défaut si clé absente/expirée
$dailyUsers = Cache::get('stats.daily_users', 0);

// 3. Motif "Remember" (Récupère ou calcule + met en cache si absent)
$categories = Cache::remember('categories.all', 86400, function() {
    return DB::table('categories')->get();
});

// 4. Vérification et suppression
if (Cache::has('stats.daily_users')) {
    Cache::delete('stats.daily_users');
}

// 5. Vidage complet du driver de cache actif
Cache::clear();

```

---

### Log

La facade `Log` interface le système de journalisation (Monolog / PSR-3) enregistré sous la clé `logger`. Elle permet de tracer le comportement de l'application selon les différents niveaux de gravité PSR-3.

```php
use Clicalmani\Core\Support\Facades\Log;

// 1. Logs d'information et de suivi
Log::info('Connexion réussie de l\'utilisateur.', ['user_id' => 42]);
Log::debug('Payload reçu de l\'API externe.', ['payload' => $rawData]);

// 2. Avertissements et faits notables
Log::warning('Tentative de connexion échouée (mot de passe incorrect).', ['ip' => Request::ip()]);
Log::notice('Changement de configuration détecté à chaud.');

// 3. Capture d'erreurs critiques avec stack trace
try {
    // Opération risquée...
} catch (\Throwable $e) {
    Log::error('Erreur critique lors du traitement de la commande.', [
        'exception' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

```

---

### Config

La facade `Config` offre un accès unifié aux options de configuration stockées dans le dossier `config/`. Elle prend en charge la notation par points (*dot notation*) pour naviguer dans les tableaux multidimensionnels.

```php
use Clicalmani\Core\Support\Facades\Config;

// 1. Lecture simple avec valeur de repli
$appName = Config::get('app.name', 'Tonka Framework');
$dbHost = Config::get('database.connections.mysql.host', '127.0.0.1');

// 2. Modification dynamique au temps d'exécution (volatilité sur la requête en cours)
Config::set('app.timezone', 'Europe/Paris');

// 3. Vérification de l'existence d'une clé
if (Config::has('services.stripe.secret')) {
    // Intégration Stripe configurée
}

// 4. Transtypage garanti (Type casting)
$isDebugEnabled = Config::boolean('app.debug');  // bool
$serverPort     = Config::integer('app.port', 8080); // int
$environment    = Config::string('app.env', 'production'); // string

```

---

### Storage

La facade `Storage` permet d'interagir avec les systèmes de fichiers locaux ou distants (S3, Cloud Storage) via le service `storage.manager`.

```php
use Clicalmani\Core\Support\Facades\Storage;
use League\Flysystem\Visibility;

// 1. Opérations sur le disque par défaut
Storage::put('reports/2026.csv', "ID,Montant\n1,500");
$content = Storage::get('reports/2026.csv');

// 2. Ciblage explicite d'un disque nommé
Storage::drive('public')->put('avatars/user_1.png', $fileBinary);
Storage::drive('s3')->setVisibility('exports/data.json', Visibility::PUBLIC);

// 3. Vérification et suppression
if (Storage::fileExists('temp/export.zip')) {
    Storage::delete('temp/export.zip');
}

// 4. Inspection de répertoires
$allPhotos = Storage::drive('public')->files('photos');

```

---

### Mail

La facade `Mail` permet d'envoyer des courriels transactionnels ou de les placer en file d'attente (queues) via le service `mailer`.

```php
use Clicalmani\Core\Support\Facades\Mail;
use App\Mails\WelcomeMail;
use App\Mails\InvoiceMail;

// 1. Envoi synchrone immédiat
Mail::to('client@example.com')
    ->cc('comptabilite@example.com')
    ->bcc('archive@example.com')
    ->send(new WelcomeMail($user));

// 2. Envoi asynchrone via la file d'attente (MessageBus / Queue)
Mail::to($user->email)->queue(new InvoiceMail($order));

```

---

## Facades Disponibles

Voici la matrice de référence associant chaque facade à sa clé de service enregistrée dans le conteneur d'injection de dépendances :

| Facade | Service dans le conteneur | Description |
| --- | --- | --- |
| `Route` | `router` | Déclaration et gestion des routes HTTP |
| `Request` | `request` | Inspection de la requête HTTP courante |
| `DB` | `database` | Query Builder et gestionnaire BDD |
| `Cache` | `cache` | Moteur de cache (Redis, File, Array) |
| `Log` | `logger` | Journalisation système (PSR-3) |
| `Config` | `config` | Récupération des options de configuration |
| `Storage` | `storage.manager` | Abstraction du système de fichiers |
| `Mail` | `mailer` | Envoi d'emails transactionnels |
| `View` | `view` | Moteur de rendu de vues (Twig / HTML) |
| `Session` | `session` | Gestion des données de session utilisateur |
| `Auth` | `auth` | Authentification et contrôle d'accès |
| `Event` | `events` | Dispatcher d'événements et écouteurs |
| `MessageBus` | `messenger` | File d'attente et traitement asynchrone |
| `Broadcast` | `broadcaster` | Diffusion d'événements en temps réel (WebSockets) |

---

## Comment ça fonctionne ?

Toutes les facades étendent la classe abstraite `Clicalmani\Core\Support\Facades\Facade`.

Une facade n'héberge **aucune logique métier** : elle définit simplement la méthode `getFacadeAccessor()`, qui retourne le nom (*binding*) du service dans le conteneur de dépendances.

Lorsque vous appelez une méthode statique inexistante sur la facade, la méthode magique `__callStatic()` intercepte l'appel, résout l'instance du service depuis le conteneur `app()` et lui transmet l'appel de méthode.

```php
// Définition de la facade
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'router';
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return static::getFacadeRoot()?->$method(...$arguments);
    }
}

// Utilisation
Route::get('/users', [UserController::class, 'index']);

// Équivalent sous-jacent à :
app()->getContainer()->get('router')->get('/users', [UserController::class, 'index']);

```

---

## Utilisation

Grâce aux facades, vous pouvez orchestrer un traitement complexe dans un contrôleur ou un service de manière fluide et lisible :

```php
namespace App\Http\Controllers;

use Clicalmani\Core\Support\Facades\Route;
use Clicalmani\Core\Support\Facades\DB;
use Clicalmani\Core\Support\Facades\Log;
use Clicalmani\Core\Support\Facades\Cache;
use Clicalmani\Core\Support\Facades\Request;

class UserController
{
    public function index()
    {
        // 1. Vérification du cache
        $cacheKey = 'users.page.' . Request::input('page', 1);

        $users = Cache::remember($cacheKey, 600, function() {
            // 2. Requête en BDD si absent du cache
            return DB::table('users')
                ->where('status = ?', ['active'])
                ->get();
        });

        // 3. Log de suivi
        Log::info('Liste des utilisateurs consultée.', [
            'count' => count($users),
            'ip'    => Request::ip()
        ]);

        return response()->json($users);
    }
}

```

---

## Création d'une Facade

Pour créer une facade personnalisée pour votre propre service applicatif :

### 1. Enregistrez votre service dans le conteneur

Vous pouvez enregistrer votre service soit via un **Service Provider**, soit directement par un **callback** lors de l'initialisation de l'application.

#### Option A : Via un Service Provider

Exécutez la commande `make:provider` pour générer la classe :

```bash
php tonka make:provider SmsServiceProvider

```

Le fichier `SmsServiceProvider.php` est créé dans le répertoire `app/Providers`. Dans la méthode `register()`, enregistrez le service, puis configurez-le au besoin dans la méthode `boot()` :

```php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProviderInterface;
use App\Services\SmsService;

class SmsServiceProvider implements ServiceProviderInterface
{
    /**
     * Enregistre le service et ses arguments dans le conteneur d'injection.
     */
    public function register(): void
    {
        $smsKey = '<yourSmsKey>';

        app()->addService(
            SmsService::class,                  // Identifiant principal
            SmsService::class,                  // Classe à instancier
            function($config) use ($smsKey) {
                $config->args([$smsKey]);       // Arguments passés au constructeur
            },
            ['sms.service', SmsService::class]  // Aliases [alias, Identifiant]
        );
    }

    /**
     * Initialise les configurations ou dépendances après enregistrement.
     */
    public function boot(?\Clicalmani\Core\Acme\Container $container = null): void 
    {
        $container->get('sms.service')->setSender('TonkaApp');
    }
}

```

Déclarez ensuite le provider dans `bootstrap/app.php` :

```php
// bootstrap/app.php

use Clicalmani\Core\Maker\Application;

return Application::setup(rootPath: dirname(__DIR__))
    // ...
    ->withService(function (Application $app) {
        $app->register(
            new \App\Providers\SmsServiceProvider()
        );
    });

```

#### Option B : Via un Callback

Si votre service ne nécessite pas de logique d'initialisation complexe dans `boot()`, enregistrez-le directement dans `bootstrap/app.php` :

```php
// bootstrap/app.php

use Clicalmani\Core\Maker\Application;

return Application::setup(rootPath: dirname(__DIR__))
    // ...
    ->withService(function(Application $app) {
        $app->addService(
            \App\Services\SmsService::class,
            \App\Services\SmsService::class,
            function($config) {
                $config->args(['<yourSmsKey>']);
            },
            ['sms.service', \App\Services\SmsService::class]
        );
    });

```

---

### 2. Créez la classe Facade

Créez la classe en étendant `Clicalmani\Core\Support\Facades\Facade` et indiquez la clé du service enregistrée dans le conteneur :

```php
namespace App\Facades;

use Clicalmani\Core\Support\Facades\Facade;

class SMS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sms.service';
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return static::getFacadeRoot()->$method(...$arguments);
    }
}

```

---

### 3. Utilisez votre Facade

Vous pouvez maintenant utiliser votre facade n'importe où dans le projet :

```php
use App\Facades\SMS;

SMS::send('+33600000000', 'Votre code de vérification est 4829.');

```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les facades :

1. **[Commandes Console](https://www.google.com/search?q=commands.md)** → Toutes les commandes

---

**Facades comprises !** 🚀 → [Commandes Console](https://www.google.com/search?q=commands.md)