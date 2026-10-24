# Service Providers

- [Service Providers](#service-providers)
- [Introduction](#introduction)
- [Providers Built-in](#providers-built-in)
  - [AppServiceProvider](#appserviceprovider)
  - [RouteServiceProvider](#routeserviceprovider)
- [Création d'un Service Provider](#creation-d-39-un-service-provider)
  - [Commande](#commande)
  - [Structure](#structure)
- [Enregistrement](#enregistrement)
  - [Méthode `register()`](#méthode-register)
  - [Méthode `boot()`](#méthode-boot)
- [Providers Personnalisés](#providers-personnalises)
  - [Exemple de Provider](#exemple-de-provider)
  - [Enregistrement](#enregistrement-1)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Les **Service Providers** sont le cœur de l'architecture de Tonka. Ils permettent d'organiser l'enregistrement des services, des événements, des middlewares et des routes de manière modulaire.

**Fonctionnalités :**

* ✅ Enregistrement de services dans le conteneur d'injection de dépendances
* ✅ Configuration du cycle de vie de l'application
* ✅ Organisation modulaire et découplée du code
* ✅ Résolution des dépendances gérée automatiquement

---

## Providers Built-in

### AppServiceProvider

Le Service Provider principal de l'application pour partager des données globales ou enregistrer des bindings génériques.

```php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProvider;
use Clicalmani\Core\Acme\Container;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistrement de services généraux
    }

    public function boot(Container $container): void
    {
        // Partage global de données via Inertia
        Inertia::share([
            'appName' => config('app.name'),
            'user' => function() {
                return auth()->user();
            }
        ]);
    }
}

```

---

### RouteServiceProvider

Gère le chargement, l'isolation et la configuration du routage web/API.

```php
namespace App\Providers;

use Clicalmani\Core\Providers\RouteServiceProvider as ServiceProvider;
use Clicalmani\Core\Acme\Container;
use Clicalmani\Core\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $api_prefix = 'api';
    protected $parameter_prefix = ':';

    public function register(): void
    {
        $this->routes(function() {
            if (Route::isApi()) {
                Route::group(fn() => require_once root_path($this->api_handler))
                    ->prefix($this->api_prefix)
                    ->middleware('api');
            } else {
                Route::group(fn() => require_once root_path($this->web_handler))
                    ->middleware('web');
            }
        });
    }

    public function boot(Container $container): void
    {
        // Contraintes par expressions régulières sur les paramètres de route
        Route::pattern('id', '[0-9]+');
    }
}

```

---

### AuthServiceProvider

Gère l'authentification et l'identification des utilisateurs connectés.

```php
namespace App\Providers;

use App\Models\AuthAccess;
use Clicalmani\Core\Auth\Authenticate;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Support\Facades\DB;

class AuthServiceProvider extends Authenticate
{
    protected string $userModel = \App\Models\User::class;

    /**
     * Récupère l'ID de l'utilisateur stocké en session.
     */
    public function getConnectedUserID(?RequestInterface $request): mixed
    {
        if ($user_id = $request->session('user:id')->get()) {
            return (int) $user_id;
        }

        return null;
    }

    // ...
}

```

> **Note :** L'instance manipulée par ce provider est retournée lorsque vous appelez `$request->user()` ou le helper `auth()`.

---

## Création d'un Service Provider

### Commande

Générez un nouveau provider via la console Tonka :

```bash
php tonka make:provider MonServiceProvider

```

### Structure

La classe générée est placée dans `app/Providers/` et adopte la structure suivante :

```php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProviderInterface;
use Clicalmani\Core\Acme\Container;

class MonServiceProvider implements ServiceProviderInterface
{
    /**
     * Enregistrement des services dans le conteneur
     */
    public function register(): void
    {
        // ...
    }

    /**
     * Initialisation post-enregistrement
     */
    public function boot(Container $container): void
    {
        // ...
    }
}

```

---

## Enregistrement

### Méthode `register()`

La méthode `register()` sert **exclusivement** à enregistrer des éléments dans le conteneur d'injection de dépendances. Ne tentez pas d'y utiliser des services enregistrés par d'autres providers, car ils pourraient ne pas être encore chargés.

```php
public function register(): void
{
    // 1. Enregistrement d'un service simple avec dépendances
    app()->addService(
        'mon.service',
        MonService::class,
        function($config) {
            $config->args([
                app()->dependency('service', 'logger')
            ]);
        }
    );

    // 2. Enregistrement avec paramètres de configuration et d'autres services
    app()->addService(
        'autre.service',
        AutreService::class,
        function($config) {
            $config->args([
                app()->dependency('param', 'app.debug'),
                app()->dependency('service', 'mon.service')
            ]);
        }
    );
}

```

---

### Méthode `boot()`

La méthode `boot()` est exécutée une fois que **tous** les Service Providers ont été enregistrés via `register()`. Vous pouvez y accéder librement à n'importe quel service du conteneur.

```php
use Clicalmani\Core\Acme\Container;
use Clicalmani\Core\Support\Facades\Route;

public function boot(Container $container): void
{
    // Accès aux services déjà enregistrés
    $service = $container->get('mon.service');

    // Modification dynamique de la configuration
    app()->config->set('mon.config', 'valeur');

    // Déclaration de middlewares ou d'événements
    Route::middleware('mon.middleware');
}

```

---

### Déclaration dans l'Application

Afin qu'un Service Provider soit exécuté par Tonka, enregistrez son instance dans le fichier `bootstrap/app.php` :

```php
// bootstrap/app.php

use Clicalmani\Core\Maker\Application;

return Application::setup(rootPath: dirname(__DIR__))
    ->withService(function(Application $app) {
        $app->register(new \App\Providers\MonServiceProvider());
    });

```

---

## Providers Personnalisés

### Exemple de Provider

Voici un exemple complet intégrant la déclaration d'un service personnalisé de paiement (`PaymentService`) avec injection de paramètres et configuration lors de la phase `boot()` :

```php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProviderInterface;
use App\Services\PaymentService;
use Clicalmani\Core\Acme\Container;

class PaymentServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        // Enregistrement du service dans le conteneur
        app()->addService(
            'payment',                             // ID du service
            PaymentService::class,                 // Classe du service
            function($config) {                    // Configurator
                $config->args([
                    app()->dependency('param', 'payment.api_key'),
                    app()->dependency('param', 'payment.secret')
                ]);
            },
            ['payment.service', PaymentService::class] // Aliases [alias, referenceId]
        );
    }

    public function boot(Container $container): void
    {
        // Configuration post-enregistrement
        $payment = $container->get('payment');
        $payment->setMode(config('payment.mode'));
    }
}

```

---

### Enregistrement
<div id="enregistrement-1"></div>

Déclarez le provider dans `bootstrap/app.php` :

```php
// bootstrap/app.php

use Clicalmani\Core\Maker\Application;

return Application::setup(rootPath: dirname(__DIR__))
    ->withService(static fn(Application $app) => $app->register(
        new \App\Providers\PaymentServiceProvider()
    ));

```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les Service Providers :

1. **[Helpers](helpers.md)** → Fonctions globales et raccourcis utilitaires
2. **[Facades](facades.md)** → Accès statique et fluide aux services
3. **[Commandes Console](commands.md)** → Référence de la CLI Tonka

---

**Service Providers compris !** 🚀 → [Helpers](helpers.md)