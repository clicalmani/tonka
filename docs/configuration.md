## Configuration

- [Introduction](#introduction)
- [Configuration de l'Environnement](#configuration-de-l-39-environnement)
- [Récupération de la Configuration d'Environnement](#recuperation-de-la-configuration-d-39-environnement)
- [Accès aux Valeurs de Configuration](#acces-aux-valeurs-de-configuration)
- [Mode Débogage de l'Application](#mode-debogage-de-l-39-application)
- [Mode Maintenance](#mode-maintenance)

## Introduction

Tous les fichiers de configuration de **Tonka** se trouvent dans le répertoire `config`. Ce répertoire contient l'ensemble des fichiers de configuration nécessaires au bon fonctionnement de l'application **Tonka**. Chaque fichier de ce répertoire est structuré pour gérer des paramètres de configuration spécifiques, garantissant ainsi un fonctionnement fluide et efficace de l'application.

Le répertoire `config` est organisé pour faciliter l'accès et la gestion des fichiers de configuration. La centralisation de tous les fichiers de configuration en un seul endroit simplifie le processus de mise à jour et de maintenance des paramètres de l'application. Cette organisation permet de rationaliser le processus de configuration et réduit les risques d'erreurs.

Dans le répertoire `config`, vous trouverez divers fichiers de configuration, chacun répondant à un objectif précis :

* **Configuration de la Base de Données** : Gère les paramètres nécessaires pour se connecter et interagir avec la base de données.
* **Configuration de l'Application** : Contient les paramètres généraux de l'application, tels que les variables d'environnement et les options spécifiques à l'application.
* **Configuration de l'Authentification** : Gère les paramètres relatifs à l'authentification et à l'autorisation des utilisateurs.
* **Configuration CORS** : Gère les paramètres de protection liés au partage de ressources entre origines multiples (CORS) afin de renseigner la sécurité.
* **Configuration du Hachage de Mots de Passe** : Contient les paramètres pour les algorithmes de hachage de mots de passe et les mesures de sécurité associées.

## Configuration de l'Environnement

Tonka utilise des fichiers `.env` pour gérer les variables d'environnement de l'application. Toutes les variables d'environnement doivent être stockées dans un fichier `.env` situé à la racine du dossier de l'application. Ce fichier contient des paires clé-valeur qui définissent les paramètres spécifiques à l'environnement pour l'application.

Voici un tableau présentant les types de variables d'environnement :

| Type de Variable | Description |
| --- | --- |
| `AP_KEY` | La clé de l'application. |
| `AP_NAME` | Le nom de l'application. |
| `DB_CONNECTION` | Le pilote de base de données par défaut utilisé par l'application. |
| `APP_ENV` | Définit l'environnement dans lequel l'application s'exécute (ex. : `development`, `production`). |
| `APP_DEBUG` | Un indicateur booléen pour activer ou désactiver le mode débogage. |

!> Assurez-vous que le fichier `.env` est inclus dans votre fichier `.gitignore` afin d'éviter d'exposer des informations sensibles dans votre système de contrôle de version.

## Récupération de la Configuration d'Environnement

Pour récupérer les valeurs de configuration de l'environnement, vous pouvez utiliser la fonction `env()`. Cette fonction vous permet d'accéder aux valeurs stockées dans votre fichier `.env`. De plus, la fonction `env()` accepte un second argument qui permet de définir une valeur par défaut dans le cas où la variable d'environnement ne serait pas définie.

Voici un exemple d'utilisation de la fonction `env()` :

```php
$databaseConnection = env('DB_CONNECTION', 'mysql');
$appDebug = env('APP_DEBUG', false);

```

Dans cet exemple, `env('DB_CONNECTION', 'mysql')` renverra la valeur de la variable d'environnement `DB_CONNECTION`, ou `'mysql'` si la variable n'est pas définie. De même, `env('APP_DEBUG', false)` renverra la valeur de la variable d'environnement `APP_DEBUG`, ou `false` si elle n'est pas définie.

## Accès aux Valeurs de Configuration

Pour accéder aux valeurs de configuration de l'application, vous pouvez utiliser la Facade `Config`. La Facade `Config` offre un moyen pratique de récupérer les valeurs de configuration définies dans vos fichiers de configuration.

Voici un exemple d'utilisation de la Facade `Config` :

```php
$databaseConnection = Config::get('database.default');
$appName = Config::get('app.name');

```

Dans cet exemple, `Config::get('database.default')` renverra la connexion de base de données par défaut définie dans le fichier de configuration `database`, et `Config::get('app.name')` renverra le nom de l'application défini dans le fichier de configuration `app`.

La Facade `Config` propose également des méthodes de récupération typées pour garantir que les valeurs de configuration sont renvoyées dans le type de données attendu. Voici les méthodes disponibles :

| Méthode | Description |
| --- | --- |
| `Config::string()` | Récupère la valeur de configuration sous forme de chaîne de caractères (*string*). |
| `Config::integer()` | Récupère la valeur de configuration sous forme d'entier (*integer*). |
| `Config::array()` | Récupère la valeur de configuration sous forme de tableau (*array*). |
| `Config::float()` | Récupère la valeur de configuration sous forme de nombre à virgule flottante (*float*). |
| `Config::boolean()` | Récupère la valeur de configuration sous forme de booléen (*boolean*). |

Ces méthodes permettent de maintenir le typage sécurisé et d'éviter les erreurs liées aux types dans votre application.

Voici un exemple d'utilisation de ces méthodes de récupération typées :

```php
$appName = Config::string('app.name');
$maxConnections = Config::integer('database.max_connections');
$services = Config::array('services');
$piValue = Config::float('math.pi');
$isDebugMode = Config::boolean('app.debug');

```

Dans cet exemple, `Config::string('app.name')` renverra le nom de l'application sous forme de chaîne, `Config::integer('database.max_connections')` renverra le nombre maximal de connexions à la base de données sous forme d'entier, `Config::array('services')` renverra la configuration des services sous forme de tableau, `Config::float('math.pi')` renverra la valeur de pi sous forme de float, et `Config::boolean('app.debug')` renverra le statut du mode débogage sous forme de booléen.

## Mode Débogage de l'Application

Pour activer ou désactiver le mode débogage dans l'application Tonka, vous pouvez utiliser la variable d'environnement `APP_DEBUG` définie dans votre fichier `.env`. Le mode débogage est utile lors du développement car il fournit des messages d'erreur détaillés et des traces d'exécution (*stack traces*), ce qui aide à identifier et à résoudre les problèmes.

### Activer le Mode Débogage

Pour activer le mode débogage, définissez la variable `APP_DEBUG` sur `true` dans votre fichier `.env` :

```
APP_DEBUG=true

```

### Désactiver le Mode Débogage

Pour désactiver le mode débogage, définissez la variable `APP_DEBUG` sur `false` dans votre fichier `.env` :

```
APP_DEBUG=false

```

### Accéder au Mode Débogage dans le Code

Vous pouvez accéder au statut du mode débogage dans votre code en utilisant la fonction `env()` ou la Facade `Config`. Voici des exemples pour chacune des méthodes :

Utilisation de la fonction `env()` :

```php
$isDebugMode = env('APP_DEBUG', false);

```

Utilisation de la Facade `Config` :

```php
$isDebugMode = Config::boolean('app.debug');

```

Dans ces deux exemples, la valeur de `APP_DEBUG` sera récupérée depuis le fichier `.env`, et vous pourrez utiliser la variable `$isDebugMode` pour exécuter du code de manière conditionnelle selon que le mode débogage est activé ou non.

En gérant correctement la variable d'environnement `APP_DEBUG`, vous contrôlez le niveau de rapport d'erreurs et d'informations de débogage affichées par votre application **Tonka**, garantissant ainsi un processus de développement et de débogage plus fluide.

## Mode Maintenance

Tonka gère le mode maintenance par le biais d'un service de route (*route service*). Un service de route est une classe qui étend la classe de base `RouteService` et implémente la méthode `redirect`. Cette méthode est chargée de gérer la redirection des requêtes lorsque l'application est en mode maintenance.

Voici un exemple de classe de service de route pour le mode maintenance :

```php
<?php

namespace App\Authenticate;

use Clicalmani\Core\Providers\RouteService;

class MaintenenceRedirect extends RouteService
{
    /**
     * Constructeur
     * 
     * @param protected \Clicalmani\Routing\Route|false $route
     */
    public function __construct(protected \Clicalmani\Routing\Route|false $route)
    {
        parent::__construct();
    }

    /**
     * Émettre une redirection
     * 
     * @return void
     */
    public function redirect()
    {
        /**
         * |-------------------------------------------------------
         * | Redirection de Maintenance
         * |-------------------------------------------------------
         * | Ici, nous définissons simplement une redirection temporaire
         * | pour toutes les requêtes. Vous pouvez effectuer une redirection
         * | en fonction de la requête ou de l'utilisateur.
         */
        if ($this->route) $this->route->redirect = 503;
    }
}

```

Dans cet exemple, la classe `MaintenenceRedirect` étend la classe de base `RouteService` et implémente la méthode `redirect` pour renvoyer une réponse **503 Service Unavailable**.

Pour activer le mode maintenance, vous pouvez enregistrer le service `MaintenenceRedirect` dans la configuration de routage de votre application. Cela permettra de s'assurer que toutes les requêtes entrantes sont redirigées vers la réponse du mode maintenance lorsque l'application est en maintenance.

En utilisant un service de route pour le mode maintenance, **Tonka** offre un moyen flexible et personnalisable de gérer les périodes de maintenance, garantissant que les utilisateurs sont informés et que les requêtes sont correctement traitées pendant l'interruption de service.

Par défaut, **Tonka** ne fournit pas de mode maintenance intégré. Pour implémenter le mode maintenance, vous devez créer manuellement un service de route et l'enregistrer dans le fichier `kernel` situé dans le dossier `bootstrap`, sous la section `tps`.

Ensuite, enregistrez votre service de route personnalisé dans le fichier `kernel` situé dans le dossier `bootstrap`. Ajoutez le service à la section `tps` :

```php
// bootstrap/kernel.php

return [
    'tps' => [
        /**
         * |-------------------------------------------------------------------
         * |                    Services Tiers de Route (Route TPS)
         * |-------------------------------------------------------------------
         * 
         */
        [
            // Autres services ...
            \App\Authenticate\MaintenenceRedirect::class,
        ],

        /**
         * |-------------------------------------------------------------------
         * |                    Services Tiers de Réponse (Response TPS)
         * |-------------------------------------------------------------------
         * 
         */
        [
            //
        ]
    ],
];

```

!> L'objet requête est rendu disponible dans un service de route via `$this->request`, ce qui vous permet d'effectuer votre redirection par requête ou par utilisateur.