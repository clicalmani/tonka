## Conteneur de Services

- [Introduction](#introduction)
- [Enregistrement des Services](#enregistrement-des-services)
- [Résolution des Services](#resolution-des-services)
- [Cycles de Vie des Services : Singleton vs Transient](#cycles-de-vie-des-services-singleton-vs-transient)
- [Avancé : Alias, Injection de Paramètres et d'Interfaces](#avance-alias-injection-de-parametres-et-d-39-interfaces)
- [Débogage et Inspection du Conteneur](#debogage-et-inspection-du-conteneur)
- [Prochaines Étapes](#prochaines-etapes)

## Introduction

Le conteneur de services de Tonka est un système d'injection de dépendances conçu pour gérer l'instanciation et le cycle de vie des services au sein d'une application. Il vous permet d'enregistrer, de résoudre et de configurer des services de manière centralisée, favorisant ainsi la modularité et la testabilité. Le conteneur prend en charge des fonctionnalités telles que la résolution automatique des dépendances et la configuration des services.

Tonka s'appuie sur le composant Symfony Dependency Injection pour gérer et injecter efficacement les dépendances. Cette approche permet un code modulaire, testable et maintenable en permettant aux services d'être définis, configurés et reliés ensemble de manière déclarative.

## Enregistrement des Services

Dans la plupart des cas, vous indiquerez les dépendances par typage (type-hint) dans les routes, les contrôleurs, les écouteurs d'événements et d'autres classes, ce qui permet au conteneur de les injecter automatiquement sans avoir besoin d'interagir directement avec celui-ci. Vous interagirez probablement avec le conteneur de services lorsque vous typierez une interface sur une route ou une classe de contrôleur et que vous souhaiterez indiquer au conteneur comment la résoudre.

Pour enregistrer des services et leurs dépendances dans le conteneur de services de Tonka, définissez-les programmatiquement à l'aide de l'API du conteneur. Chaque service peut spécifier sa classe, ses arguments de constructeur (dépendances) et son cycle de vie (singleton ou transient).

```php
// bootstrap/app.php
use \Clicalmani\Core\Maker\Application;
return Application::setup(rootPath: dirname(__DIR__))
    ->withService(function(Application $app) {
        $app->addService(
            // ID du service
            // Classe du service
            // Configuration du service
        );
    });
```

Les dépendances sont injectées automatiquement en fonction de la configuration, garantissant que chaque service reçoit les instances requises.

> Pour plus d'informations sur l'enregistrement d'un service, consultez la section [Fournisseur de Services](providers.md).

## Résolution des Services

Une fois les services enregistrés, vous pouvez les résoudre depuis le conteneur selon vos besoins. Le conteneur injecte automatiquement les dépendances conformément à la configuration.

**Exemple :**
```php
// Récupérer le service de dépôt utilisateur
$userRepository = container()->get('app.user_repository');
```

Cette approche garantit que chaque service est construit avec ses dépendances requises, favorisant un couplage lâche et des tests plus faciles.

### Cycles de Vie des Services : Singleton vs Transient

Le conteneur de services de Tonka prend en charge deux cycles de vie principaux :

- **Singleton** : Le conteneur crée une seule instance du service et renvoie la même instance à chaque fois qu'elle est demandée. C'est le comportement par défaut.
- **Transient** : Le conteneur crée une nouvelle instance du service à chaque fois qu'elle est demandée.

```php
// Singleton (par défaut)
$app->addService(function($config) {
    'app.singleton_service', 
    App\Service\SingletonService::class,
    function($config) {
        $config->share(false); // Comportement par défaut
    }
});

// Transient
$app->addService(function($config) {
    'app.singleton_service', 
    App\Service\SingletonService::class,
    function($config) {
        $config->share(true); // Activer le mode transient
    }
});
```

Choisir le cycle de vie approprié permet de gérer efficacement les ressources et le comportement de l'application.

### Avancé : Alias, Injection de Paramètres et d'Interfaces

Le conteneur de services de Tonka prend également en charge les alias de services et l'injection de paramètres pour une plus grande flexibilité.

**Alias de Services :**  
Vous pouvez définir des noms alternatifs pour les services, ce qui vous permet de remplacer facilement les implémentations.

```php
$app->addService(function($config) {
    'app.singleton_service', 
    App\Service\SingletonService::class,
    function($config) {
        // Configuration du service
    },
    ['singleton_service', App\Service\SingletonService::class] // Alias
});
```

Désormais, résoudre `singleton_service` renverra le service `app.singleton_service`.

**Injection de Paramètres :**  
Injectez des valeurs de configuration ou des variables d'environnement dans vos services à l'aide de paramètres.

```php
$app->addService(function($config) {
    'app.mail', 
    App\Service\SingletonService::class,
    function($config) {
        $config->args([
            [config('app.mailer_transport')]
        ]);
    }
});
```

**Injection d'Interfaces :**
Vous pouvez typer une interface sur une route ou une classe de contrôleur ; le conteneur de services résoudra automatiquement la dépendance en recherchant dans le même chemin. Si votre interface est située dans un chemin différent, vous devez indiquer au conteneur de services comment la résoudre. Vous pouvez utiliser la méthode `injectInterfaces` dans votre AppServiceProvider pour résoudre vos dépendances d'interface :

```php
// app/Provider/AppServiceProvider.php

use Clicalmani\Core\Acme\Container;

public function boot(Container $container) : void
{
    Container::injectInterfaces([
        MailTransportInterface::class => TransportInterface::class
    ]);
}
```

Cette approche maintient votre configuration flexible et adaptée à l'environnement.

### Débogage et Inspection du Conteneur

Pour déboguer ou inspecter les services et paramètres enregistrés, utilisez la méthode helper `container()` :

```php
// Lister tous les ID de services
$serviceIds = container()->getServiceIds();

// Vérifier si un service existe
if (container()->has('app.mailer')) {
    // Le service est enregistré
}
```

Ces outils vous aident à vérifier votre configuration et à résoudre les problèmes efficacement.

---

## Prochaines Étapes

Le conteneur de services de Tonka étant désormais posée, les ressources suivantes permettent d'approfondir sa mise en œuvre pratique :

1. **[Routing](routing.md)** → Définir et structurer vos routes
2. **[Contrôleurs](controllers.md)** → Implémenter la logique métier
3. **[Base de Données](database.md)** → Exploiter les modèles Elegant ORM
4. **[Vues](views.md)** → Concevoir l'interface utilisateur

---

**Conteneur de services comprise !** 🚀 → [Routing](routing.md)