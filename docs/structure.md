# Structure du Projet

Cette section décrit l'architecture standard d'un projet Tonka et le rôle de chacun de ses répertoires. Une bonne compréhension de cette organisation est un prérequis pour naviguer efficacement dans le code source, respecter les conventions du framework et tirer parti de ses mécanismes d'autoloading et de découverte automatique des composants.

- [Structure du Projet](#structure-du-projet)
- [Structure Globale](#structure-globale)
- [Dossier `app`](#dossier-code-app-code-)
  - [`app/Console`](#-code-app-console-code-)
  - [`app/Http`](#-code-app-http-code-)
  - [`app/Models`](#-code-app-models-code-)
  - [`app/Providers`](#-code-app-providers-code-)
  - [`app/Tasks`](#-code-app-tasks-code-)
- [Dossier `bootstrap`](#dossier-code-bootstrap-code-)
- [Dossier `config`](#dossier-code-config-code-)
- [Dossier `database`](#dossier-code-database-code-)
  - [`database/entities`](#-code-database-entities-code-)
  - [`database/factories`](#-code-database-factories-code-)
  - [`database/manifests`](#-code-database-manifests-code-)
  - [`database/routines`](#-code-database-routines-code-)
  - [`database/seeders`](#-code-database-seeders-code-)
- [Dossier `public`](#dossier-code-public-code-)
- [Dossier `resources`](#dossier-code-resources-code-)
- [Dossier `routes`](#dossier-code-routes-code-)
- [Dossier `storage`](#dossier-code-storage-code-)
- [Dossier `tests`](#dossier-code-tests-code-)
- [Fichiers Racine](#fichiers-racine)
- [Prochaines Étapes](#prochaines-etapes)

---

## Structure Globale

Un projet Tonka généré via l'installateur suit une arborescence conventionnelle inspirée des architectures MVC modernes. Chaque répertoire a une responsabilité unique et prévisible, ce qui facilite la maintenance et l'intégration de nouveaux contributeurs :

```
mon-projet/
├── app/
│   ├── Console/
│   │   └── Commands/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middlewares/
│   │   ├── Requests/
│   │   └── Kernel.php
│   ├── Models/
│   ├── Providers/
│   └── Tasks/
├── bootstrap/
│   ├── app.php
│   └── kernel.php
├── config/
│   ├── app.php
│   ├── database.php
│   ├── routing.php
│   └── ...
├── database/
│   ├── entities/
│   ├── factories/
│   ├── manifests/
│   ├── routines/
│   │   ├── functions/
│   │   ├── procedures/
│   │   └── views/
│   └── seeders/
├── public/
│   └── index.php
├── resources/
│   ├── views/
│   ├── js/
│   └── css/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── modules/
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── test/
│   └── config/
├── .env
├── composer.json
└── php tonka
```

---

## Dossier `app`

Le répertoire `app` héberge le cœur applicatif : logique métier, contrôleurs, modèles et services. C'est le point d'entrée naturel pour toute évolution fonctionnelle du projet.

### `app/Console`

Regroupe les commandes console personnalisées, exécutables via l'interface en ligne de commande `php tonka`. Ces commandes sont idéales pour l'automatisation de tâches récurrentes (imports, maintenance, génération de rapports, etc.).

Génération d'une nouvelle commande :

```bash
php tonka make:command MaCommande
```

**Structure générée :**
```
app/Console/
└── Commands/
    └── MaCommande.php
```

**Exemple d'implémentation :**
```php
namespace App\Console\Commands;

use Clicalmani\Console\Commands\Command;

class MaCommande extends Command
{
    protected function execute($input, $output)
    {
        $output->writeln('Hello World!');
        return Command::SUCCESS;
    }
}
```

---

### `app/Http`

Regroupe l'ensemble des composants intervenant dans le cycle de vie d'une requête HTTP : contrôleurs, middlewares, validation des entrées et configuration du kernel HTTP.

#### `app/Http/Controllers`

Contient les contrôleurs, responsables de l'orchestration entre les requêtes entrantes, la couche métier et les réponses renvoyées au client.

Génération d'un contrôleur de type ressource (CRUD) :

```bash
php tonka make:controller UserController --resource
```

**Structure générée :**
```
app/Http/Controllers/
├── Controller.php
├── AuthController.php
└── UserController.php
```

**Exemple d'implémentation :**
```php
namespace App\Http\Controllers;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }
}
```

#### `app/Http/Middlewares`

Contient les middlewares personnalisés, chargés d'intercepter et de traiter les requêtes avant (ou après) leur traitement par le contrôleur cible — authentification, autorisation, journalisation, etc.

Génération d'un middleware :

```bash
php tonka make:middleware AuthMiddleware
```

**Structure générée :**
```
app/Http/Middlewares/
├── AuthMiddleware.php
└── VerifyCsrfToken.php
```

#### `app/Http/Requests`

Centralise les Form Requests, dédiées à la validation et à l'autorisation des données entrantes en amont de la logique métier des contrôleurs.

Génération d'une Form Request :

```bash
php tonka make:request UserRequest
```

**Structure générée :**
```
app/Http/Requests/
└── UserRequest.php
```

#### `app/Http/Kernel.php`

Point de configuration central du pipeline de middlewares. Il définit les groupes de middlewares (`web`, `api`, etc.) ainsi que leur ordre d'exécution.

```php
class Kernel extends HttpKernel
{
    protected array $middleware = [
        'web' => [
            'auth' => AuthMiddleware::class
        ],
        'api' => [
            'api' => ApiMiddleware::class
        ]
    ];
}
```

---

### `app/Models`

Contient les modèles Elegant ORM, qui constituent l'interface programmatique entre l'application et les tables de la base de données.

Génération d'un modèle :

```bash
php tonka make:model User
```

**Structure générée :**
```
app/Models/
└── User.php
```

**Exemple d'implémentation :**
```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class User extends Elegant
{
    protected $table = "users u";
    protected string $entity = UserEntity::class;
}
```

### `app/Providers`

Regroupe les Service Providers, responsables de l'enregistrement et de l'amorçage (bootstrapping) des services de l'application au démarrage du framework.

**Structure :**
```
app/Providers/
├── AppServiceProvider.php
├── RouteServiceProvider.php
└── EventServiceProvider.php
```

### `app/Tasks`

Héberge les tâches planifiées (scheduled tasks), exécutées périodiquement via le planificateur du framework et configurées dans `config/scheduler.php`.

**Structure :**
```
app/Tasks/
└── SendDailyReportTask.php
```

---

## Dossier `bootstrap`

Contient les fichiers d'amorçage nécessaires à l'initialisation de l'application, avant que celle-ci ne soit en mesure de traiter la moindre requête.

### `bootstrap/app.php`

Point d'entrée du framework. Ce fichier instancie l'application, enregistre les kernels disponibles et retourne l'instance prête à traiter les requêtes.

```php
$app = Clicalmani\Core\Maker\Application::setup()
    ->withKernels()
    ->run();

return $app;
```

### `bootstrap/kernel.php`

Fichier de configuration déclarant les commandes console personnalisées et les Service Providers à charger au démarrage.

```php
return [
    'commands' => [
        // Commandes console personnalisées
    ],
    'providers' => [
        // Service Providers
    ]
];
```

---

## Dossier `config`

Centralise l'ensemble des fichiers de configuration de l'application. Chaque fichier expose un tableau associatif structuré, généralement alimenté par des variables d'environnement définies dans `.env`.

```
config/
├── app.php          # Configuration générale de l'application
├── database.php     # Connexions à la base de données
├── routing.php      # Comportement du routeur
├── session.php      # Gestion des sessions
├── mail.php         # Envoi d'e-mails
├── cache.php        # Système de cache
├── filesystems.php  # Disques et stockage de fichiers
├── auth.php         # Authentification
├── messenger.php    # Files d'attente (queues)
├── scheduler.php    # Tâches planifiées
├── broadcaster.php  # Diffusion d'événements (broadcasting)
└── driftql.php       # Configuration DriftQL
```

### Exemple — `config/app.php`

```php
return [
    'name' => env('APP_NAME', 'Tonka'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'fr',
];
```

### Exemple — `config/database.php`

```php
return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_NAME', 'tonka'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_TABLE_PREFIX', ''),
        ],
    ],
];
```

---

## Dossier `database`

Regroupe l'ensemble des artefacts liés à la persistance des données : définition du schéma, peuplement, versionnement et logique SQL avancée.

### `database/entities`

Contient les entités, qui décrivent la structure des tables (colonnes, types, contraintes) indépendamment de la logique métier portée par les modèles Elegant ORM.

**Structure :**
```
database/entities/
├── UserEntity.php
└── PostEntity.php
```

### `database/factories`

Contient les factories, utilisées pour générer des jeux de données réalistes à des fins de test ou de peuplement de la base.

**Structure :**
```
database/factories/
├── UserFactory.php
└── PostFactory.php
```

### `database/manifests`

Contient les manifestes XML permettant de versionner le schéma de la base de données de façon déclarative — une alternative aux migrations PHP.

**Structure :**
```
database/manifests/
├── manifest.xml
└── v1.0.0.xml
```

### `database/migrations`

Contient les migrations PHP, approche procédurale et incrémentale pour faire évoluer le schéma de la base de données de manière traçable et réversible.

**Structure :**
```
database/migrations/
├── 2024_01_01_000000_create_users_table.php
└── 2024_01_02_000000_create_posts_table.php
```

### `database/routines`

Contient les routines SQL — fonctions, procédures stockées et vues — définies de manière programmatique et synchronisées avec la base de données.

```
database/routines/
├── functions/
│   └── userExists.php
├── procedures/
│   └── updateStatus.php
└── views/
    └── userSummary.php
```

### `database/seeders`

Contient les seeders, responsables du peuplement initial ou de démonstration de la base de données.

**Structure :**
```
database/seeders/
├── UserSeeder.php
└── PostSeeder.php
```

---

## Dossier `public`

Constitue le seul répertoire exposé publiquement par le serveur web. Il joue le rôle de point d'entrée unique pour l'ensemble des requêtes HTTP adressées à l'application.

```
public/
├── index.php          # Point d'entrée HTTP
├── .htaccess           # Configuration Apache (réécriture d'URL)
└── build/              # Assets front-end compilés (JS, CSS)
```

### `public/index.php`

```php
require_once __DIR__ . '/../bootstrap/app.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$request = \Clicalmani\Core\Http\RequestInterface::current();

$response = $app->handleRequest($request);

$response->send();
```

---

## Dossier `resources`

Regroupe les ressources brutes de l'application, c'est-à-dire les fichiers destinés à être transformés ou compilés avant d'être servis au client (vues, scripts, feuilles de style).

```
resources/
├── views/              # Templates Twig
│   ├── app.twig
│   └── emails/
├── js/                 # Sources JavaScript / TypeScript
│   ├── app.tsx
│   ├── Pages/
│   └── Components/
├── css/                # Feuilles de style
│   └── app.css
└── sass/               # Sources SASS/SCSS
    └── app.scss
```

---

## Dossier `routes`

Centralise la définition des points d'entrée de l'application, organisés par contexte (web, API, console, WebSocket) et éventuellement par module fonctionnel.

```
routes/
├── web.php             # Routes web
├── api.php             # Routes API
└── modules/            # Routes regroupées par module
    ├── blog.php
    └── admin.php
```

### Exemple — `routes/web.php`

```php
use Clicalmani\Core\Support\Facades\Route;

Route::get('/', function() {
    return inertia('Home');
})->name('home');

Route::resource('users', UserController::class);
```

---

## Dossier `storage`

Regroupe l'ensemble des fichiers générés à l'exécution : contenus utilisateurs, caches applicatifs, sessions, vues compilées et journaux d'événements. Ce répertoire nécessite des droits d'écriture pour le serveur web et ne doit jamais être versionné dans son intégralité.

```
storage/
├── app/                # Fichiers déposés par les utilisateurs
│   ├── public/
│   └── private/
├── framework/          # Artefacts internes du framework
│   ├── cache/
│   ├── sessions/
│   └── views/
├── logs/                # Journaux applicatifs
│   └── app.log
└── errors/               # Journalisation des erreurs
```

---

## Dossier `tests`

Contient les suites de tests automatisés de l'application, garantissant la non-régression des fonctionnalités au fil des évolutions du code.

```
test/
└── config/
    └── database.php
└── bootstrap.php
└── console
```

---

## Fichiers Racine

### `.env`

Fichier de variables d'environnement, distinct par déploiement et **volontairement exclu du contrôle de version**. Il centralise les paramètres sensibles ou dépendants de l'environnement d'exécution.

```env
APP_NAME=Tonka
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=tonka
DB_USER=root
DB_PASSWORD=
```

### `composer.json`

Déclare les dépendances PHP du projet ainsi que les métadonnées Composer (autoloading, scripts, contraintes de version).

```json
{
    "require": {
        "clicalmani/console": "^2.5.8",
        "clicalmani/cookie": "^1.1.2",
        "clicalmani/database": "^3.1.8",
        "clicalmani/foundation": "^3.2.0",
        "clicalmani/psr": "^1.0.8",
        "clicalmani/routing": "^2.2.5",
        "clicalmani/validation": "^3.1.0",
        "clicalmani/xpower": "^1.2.3"
    }
}
```


### `php tonka`

Exécutable en ligne de commande, point d'accès unique à l'ensemble des commandes Artisan-like fournies par le framework.

```bash
php tonka [commande] [options]
```

---

## Prochaines Étapes

Maintenant que la structure du projet vous est familière, poursuivez votre montée en compétence avec les ressources suivantes :

1. **[Architecture](architecture.md)** → Comprendre le fonctionnement interne du framework
2. **[Routing](routing.md)** → Définir et organiser vos premières routes
3. **[Contrôleurs](controllers.md)** → Implémenter la logique métier de votre application
4. **[Base de Données](database.md)** → Configurer les connexions et modéliser vos données

---

**Structure comprise !** 🚀 → [Architecture](architecture.md)