## Service Container

Tonka's service container is a dependency injection system designed to manage the instantiation and lifecycle of services within an application. It allows you to register, resolve, and configure services in a centralized manner, promoting modularity and testability. The container supports features such as singleton and transient lifetimes, automatic dependency resolution, and service configuration.

Tonka leverages the Symfony Dependency Injection component to manage and inject dependencies efficiently. This approach enables modular, testable, and maintainable code by allowing services to be defined, configured, and wired together declaratively.

### Registering Services

In most cases, you will type-hint dependencies in routes, controllers, event listeners, and other classes, allowing the container to inject them automatically without needing to interact with the container directly. You would probably interact with the sercice container when type-hinting an interface on a route or a controller class and you would like to tell the container how to resolve it.

To register services and their dependencies in Tonka's service container, define them programmatically using the container API. Each service can specify its class, constructor arguments (dependencies), and lifecycle (singleton or transient).

```php
$container = \Clicalmani\Foundation\Acme\Container::getInstance();
$container->set('app.database_connection', App\Database\Connection::class)
        ->arg(config('database.database_url'));
```

Dependencies are injected automatically based on the configuration, ensuring each service receives the required instances.

> For further information about registering a service, read the [Testing](testing.md) documentation.

### Resolving Services

Once services are registered, you can resolve them from the container as needed. The container automatically injects dependencies according to the configuration.

**Example:**
```php
// Retrieve the user repository service
$userRepository = $container->get('app.user_repository');
```

This approach ensures that each service is constructed with its required dependencies, promoting loose coupling and easier testing.

### Service Lifetimes: Singleton vs Transient

Tonka's service container supports two primary service lifetimes:

- **Singleton**: The container creates a single instance of the service and returns the same instance every time it is requested. This is the default behavior.
- **Transient**: The container creates a new instance of the service each time it is requested.

```php
// Singleton (default)
$container->register('app.singleton_service', App\Service\SingletonService::class);

// Transient
$container->register('app.transient_service', App\Service\TransientService::class)
          ->setShared(false);
```

Choosing the appropriate lifetime helps manage resources and application behavior effectively.

### Advanced: Aliases, Parameter and interface Injection

Tonka's service container also supports service aliases and parameter injection for greater flexibility.

**Service Aliases:**  
You can define alternative names for services, allowing you to swap implementations easily.

```php
$container->set('mailer', App\Service\SmtpMailer::class)
    ->alias('app.mailer');
```

Now, resolving `mailer` will return the `app.mailer` service.

**Parameter Injection:**  
Inject configuration values or environment variables into your services using parameters.

```php
$container->set('app.mail', App\Service\Mailer::class)
    ->args([config('app.mailer_transport')])
```

**Interface Injection:**
You may type-hint an interface on a route or a controller class, the service container will automatically resolve the dependence by looking in the same path. If your interface is location in a different path you must instruct the service container how to resolve it. You may use `injectInterfaces` method in your AppServiceProvider to your resolve your interface dependencies:

```php
// app/Provider/AppServiceProvider.php

use Clicalmani\Foundation\Acme\Container;

public function boot() : void
{
    Container::injectInterfaces([
        MailTransportInterface::class => TransportInterface::class
    ]);
}
```

This approach keeps your configuration flexible and environment-specific.

### Debugging and Inspecting the Container

To debug or inspect registered services and parameters, use the container's built-in methods or Symfony's console commands (if available):

```php
// List all service IDs
$serviceIds = $container->getServiceIds();

// Check if a service exists
if ($container->has('app.mailer')) {
    // Service is registered
}
```

These tools help you verify your configuration and troubleshoot issues efficiently.
