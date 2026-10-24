# Architecture

Cette section détaille les mécanismes internes du framework Tonka : le cycle de vie d'une requête, le rôle du conteneur de services, l'organisation en kernels et l'articulation entre les différentes couches applicatives. Elle s'adresse aux développeurs souhaitant comprendre, au-delà de l'usage courant, *comment* et *pourquoi* le framework se comporte de la sorte.

- [Architecture](#architecture)
- [Cycle de Vie d'une Requête](#cycle-de-vie-d-39-une-requete)
- [Le Conteneur de Services](#le-conteneur-de-services)
- [Les Kernels](#les-kernels)
  - [AppKernel](#appkernel)
  - [BootstrapKernel](#bootstrapkernel)
  - [HttpKernel](#httpkernel)
  - [ResourcesKernel](#resourceskernel)
- [L'Application](#l-39-application)
- [Les Service Providers](#les-service-providers)
- [Le Routing](#le-routing)
- [Le Modèle MVC](#le-modele-mvc)
- [Prochaines Étapes](#prochaines-etapes)

---

## Cycle de Vie d'une Requête

Chaque requête HTTP adressée à une application Tonka traverse une séquence d'étapes déterministe, depuis sa réception jusqu'à l'émission de la réponse. Comprendre cette séquence est essentiel pour situer précisément où intervenir — middleware, validation, logique métier — selon le besoin.

```
1. Point d'entrée (public/index.php)
   ↓
2. Bootstrap (bootstrap/app.php)
   ↓
3. Application::setup()
   ↓
4. Chargement des Kernels
   ↓
5. Routing (Route::dispatch())
   ↓
6. Middlewares (Pipeline)
   ↓
7. Contrôleur
   ↓
8. Validation (Request/FormRequest)
   ↓
9. Traitement (Contrôleur → Modèle)
   ↓
10. Réponse (View/JSON/Redirect)
```

### Schéma du Cycle de Vie

```
┌─────────────────────────────────────────────────────────────────┐
│                         PUBLIC/INDEX.PHP                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       BOOTSTRAP/APP.PHP                        │
│  • Chargement de l'application                                 │
│  • Configuration des services                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION::SETUP()                        │
│  • Création de l'instance                                      │
│  • withKernels() → Chargement des 4 kernels                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                          ROUTING                               │
│  • Résolution de la route (Builder)                           │
│  • Extraction des paramètres                                  │
│  • Vérification des middlewares                               │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        MIDDLEWARES                             │
│  • Web gateway → Session, CSRF, Auth                          │
│  • API gateway → Rate limiting, CORS, JWT                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        CONTROLLER                              │
│  • Méthode invoquée                                           │
│  • Injection de dépendances                                   │
│  • Validation (Form Request)                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         RESPONSE                               │
│  • View (Inertia/Twig)                                        │
│  • JSON (API)                                                 │
│  • Redirect                                                   │
└─────────────────────────────────────────────────────────────────┘
```

Chaque étape du schéma correspond à une responsabilité isolée : le point d'entrée ne fait qu'amorcer l'application, le routeur ne fait que résoudre la route, les middlewares ne font qu'intercepter, et ainsi de suite. Cette séparation stricte des préoccupations (*separation of concerns*) facilite le débogage et les tests unitaires ciblés.

---

## Le Conteneur de Services

Le conteneur centralise l'instanciation et la résolution des dépendances de l'application, évitant le couplage fort entre les composants et favorisant l'injection de dépendances plutôt que leur instanciation directe.

### Activer un service intégré

```php
// Dans bootstrap/app.php
return \Clicalmani\Core\Maker\Application::setup(rootPath: dirname(__DIR__))
        ->withMailer()
        ->withEvents()
        ->run();
```

### Enregistrer un service personnalisé

```php
// Dans bootstrap/app.php
return Application::setup(rootPath: dirname(__DIR__))
        ->withMailer()
        ->withService(static function(Application $app) {
            $app->addService(
                'ID du service',     // mon.service
                'Classe du service', // MonService::class
                static function(ServiceConfigurator|DefaultsConfigurator $config) {
                    // Instancié strictement pour rendre le service visible dès l'initialisation du conteneur
                }
            );
        });
```

### Récupération d'un service

```php
// Via le conteneur
$service = container()->get('mon.service');
```

### Services intégrés disponibles

| Service | Description |
|---------|-------------|
| `withMailer` | Envoi d'e-mails via Symfony Mailer |
| `withInertia` | Intégration InertiaJS pour les applications SPA |
| `withMessenger` | Gestion de files d'attente via Symfony Messenger |
| `withScheduler` | Planification de tâches via Symfony Scheduler |
| `withCache` | Mise en cache via le composant Symfony Cache |
| `withEvents` | Émission et écoute d'événements via Symfony Event Dispatcher |
| `withEventBroadcasting` | Diffusion d'événements en temps réel via Mercure |
| `withNotification` | Envoi de notifications multi-canaux (mail, SMS, base de données) — utile pour les alertes utilisateur et les flux asynchrones |
| `withMetrics` | Collecte et exposition de métriques applicatives (compteurs, jauges, histogrammes) pour l'observabilité et le monitoring |
| `withQueue` | Abstraction de files d'attente pour le traitement différé des tâches (jobs), avec workers et gestion des échecs |

---

## Les Kernels

Tonka structure son amorçage autour de **quatre kernels**, chacun responsable d'un domaine de configuration distinct. Cette approche modulaire permet d'isoler les responsabilités d'initialisation et de faciliter l'extension du framework sans en altérer le cœur.

### AppKernel

Responsable du chargement de la configuration générale de l'application (paramètres applicatifs, configuration du mailer, etc.) et de son injection dans le conteneur.

```php
class AppKernel extends Kernel
{
    public function register(): void
    {
        // Charge les fichiers de configuration
        $this->app_config = require config_path('/app.php');
        $this->mail_config = require config_path('/mail.php');
    }

    public function boot(): void
    {
        $this->app->config['app'] = $this->app_config;
        $this->app->config['mail'] = $this->mail_config;
    }
}
```

### BootstrapKernel

Responsable du chargement de la configuration d'amorçage définie dans `bootstrap/kernel.php` (commandes console et Service Providers à enregistrer).

```php
class BootstrapKernel extends Kernel
{
    public function register(): void
    {
        $this->bootstrap = require bootstrap_path('/kernel.php');
    }

    public function boot(): void
    {
        $this->app->config['bootstrap'] = $this->bootstrap;
    }
}
```

### HttpKernel

Responsable de la configuration et de l'enregistrement des middlewares HTTP, répartis entre les groupes `web` et `api`.

```php
class HttpKernel extends Kernel
{
    protected array $middleware = [
        'web' => [],
        'api' => []
    ];

    public function register(): void
    {
        // Configuration des middlewares web et api
    }

    public function boot(): void
    {
        $this->app->config['http'] = $this->middleware;
    }
}
```

### ResourcesKernel

Responsable de la gestion des ressources de présentation — vues, templates Twig et tags personnalisés associés au moteur de rendu.

```php
class Kernel extends BaseKernel
{
    public static array $template_tags = [
        CSRFTokenField::class,
        IfTag::class,
        EndIfTag::class,
    ];

    public function boot(): void
    {
        // Enregistrement des tags Twig
        $this->extension->addFunction(...);
    }
}
```

---

## L'Application

L'instance `Application` constitue le point central de Tonka : elle orchestre l'amorçage, expose le conteneur de services et fournit un ensemble de fonctions utilitaires (*helpers*) facilitant l'accès aux ressources du projet.

### Point d'entrée

```php
// bootstrap/app.php
$app = Application::setup()
    ->withKernels()
    ->run();
```

### Configuration

```php
// Dans un Service Provider
app()->config->set('key', 'value');

// Récupération
$value = app()->config('key', 'default');
```

### Fonctions utilitaires (helpers)

```php
// Instance de l'application
$app = app();

// Chemin racine du projet
$root = root_path();

// Chemins vers des dossiers spécifiques
$appPath = app_path('Http/Controllers');
$configPath = config_path('app.php');
$storagePath = storage_path('logs');
```

---

## Les Service Providers

Les Service Providers constituent le point d'extension privilégié du framework : ils centralisent l'enregistrement et l'initialisation des services propres à l'application, qu'ils soient fournis par Tonka ou définis par le développeur.

### Création d'un provider

```bash
php tonka make:provider MonServiceProvider
```

### Structure

```php
<?php

namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProviderInterface;

class MonServiceProvider implements ServiceProviderInterface
{
    public function register(): void
    {
        // Enregistrement des services
        app()->addService('mon.service', [
            MonService::class,
            function($config) {
                // Configuration du service
            }
        ]);
    }

    public function boot(): void
    {
        // Logique exécutée après l'enregistrement de tous les providers
    }
}
```

La méthode `register()` doit se limiter à l'enregistrement des liaisons dans le conteneur ; toute logique dépendant d'autres services doit être différée dans `boot()`, appelée une fois l'ensemble des providers enregistré.

### Enregistrement

```php
// bootstrap/app.php
use Clicalmani\Core\Maker\Application;
return Application::setup(rootPath: dirname(__DIR__))
    ->withService(function(Application $app) {
        $app->register(new MonServiceProvider);
    });
```

---

## Le Routing

Le système de routing de Tonka a été conçu pour être extensible : la syntaxe de définition des routes n'est pas figée et peut être adaptée aux préférences de l'équipe via le mécanisme des *builders*.

### Builders

```php
// config/routing.php
'default_builder' => BasicBuilder::class, // Le builder par défaut adopte une syntaxe inspirée d'ExpressJS
'builders' => [
    LaravelStyleBuilder::class,           // Syntaxe inspirée de Laravel
    CustomBuilder::class,                 // Syntaxe personnalisée
];
```

### Paramètres de route

```php
// Syntaxe Tonka (par défaut)
Route::get('/users/:id', ...);

// Syntaxe Laravel (via un builder personnalisé)
Route::get('/users/{id}', ...);
```

### Middlewares

```php
// Portée globale
// app/Http/Kernel.php
protected array $middleware = [
    'web' => [
        'auth' => AuthMiddleware::class
    ]
];

// Chargement des routes associées au middleware
// Dans routes/web.php
Route::middleware('auth'); // Middleware appliqué à l'ensemble du fichier de routes

// Portée groupe
Route::group(function() {
    // Routes protégées
})->prefix('admin')
->middleware('admin');
```

---

## Le Modèle MVC

Tonka repose sur une implémentation moderne du patron d'architecture **Modèle-Vue-Contrôleur (MVC)**, garantissant une séparation claire entre la persistance des données, la présentation et la logique de traitement des requêtes.

### Modèle (Model)

```php
// app/Models/User.php
class User extends Elegant
{
    protected string $table = "users AS u";
    protected string $entity = UserEntity::class;
}
```

### Vue (View)

```php
// Réponse HTML brute
return response()->send('HTML code or text');

// Rendu via Twig
return view('users.index', ['users' => $users]);

// Rendu via Inertia
return inertia('User/Index', ['users' => $users]);
```

### Contrôleur (Controller)

```php
// app/Http/Controllers/UserController.php
class UserController extends Controller
{
    public function index(): ViewInterface
    {
        $users = User::all();
        return view('users.index', ['users' => $users]);
    }
}
```

### Routeur

```php
// routes/web.php
Route::resource('users', UserController::class);
```

---

## Prochaines Étapes

L'architecture de Tonka étant désormais posée, les ressources suivantes permettent d'approfondir sa mise en œuvre pratique :

1. **[Routing](routing.md)** → Définir et structurer vos routes
2. **[Contrôleurs](controllers.md)** → Implémenter la logique métier
3. **[Base de Données](database.md)** → Exploiter les modèles Elegant ORM
4. **[Vues](views.md)** → Concevoir l'interface utilisateur

---

**Architecture comprise !** 🚀 → [Routing](routing.md)