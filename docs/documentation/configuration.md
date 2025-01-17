## Introduction

All Tonka configuration files reside in the `config` directory. This directory contains all the necessary configuration files required for the proper functioning of the Tonka application. Each file within this directory is structured to handle specific configuration settings, ensuring that the application runs smoothly and efficiently.

The `config` directory is organized to facilitate easy access and management of configuration files. By centralizing all configuration files in one location, it simplifies the process of updating and maintaining the application's settings. This organization helps in keeping the configuration process streamlined and reduces the chances of errors.

In the `config` directory, you will find various configuration files, each serving a specific purpose:

- **Database Configuration**: Manages the settings required to connect and interact with the database.
- **Application Configuration**: Contains general settings for the application, such as environment variables and application-specific options.
- **Authentication Configuration**: Handles the settings related to user authentication and authorization.
- **CORS Configuration**: Manages the settings for Cross-origin Resource Sharing protection to enhance security.
- **Password Hashing Configuration**: Contains the settings for password hashing algorithms and related security measures.

## Environment Configuration

Tonka uses `.env` files to manage application environment variables. All environment variables should be stored in a `.env` file located at the root of the application folder. This file contains key-value pairs that define the environment-specific settings for the application.

Here is a table presenting the types of environment variables:

| Variable Type       | Description                                                                 |
|---------------------|-----------------------------------------------------------------------------|
| `AP_KEY`           | The application key.                          |
| `AP_NAME`           | The application name |
| `DB_CONNECTION`      | The default database driver used by application.                           |
| `APP_ENV`           | Defines the environment in which the application is running (e.g., `development`, `production`). |
| `APP_DEBUG`             | A boolean flag to enable or disable debug mode.                             |

!> Ensure that the `.env` file is included in your `.gitignore` to prevent sensitive information from being exposed in version control.

## Retrieving Environment Configuration

To retrieve environment configuration values, you can use the `env()` function. This function allows you to access the values stored in your `.env` file. Additionally, the `env()` function provides a second argument that can be used to set a default value in case the environment variable is not set.

Here is an example of how to use the `env()` function:

```php
$databaseConnection = env('DB_CONNECTION', 'mysql');
$appDebug = env('APP_DEBUG', false);
```

In this example, `env('DB_CONNECTION', 'mysql')` will return the value of the `DB_CONNECTION` environment variable, or `'mysql'` if the variable is not set. Similarly, `env('APP_DEBUG', false)` will return the value of the `APP_DEBUG` environment variable, or `false` if the variable is not set.

## Accessing Configuration Values

To access the application configuration values, you can use the `Config` Facade. The `Config` Facade provides a convenient way to retrieve configuration values defined in your configuration files.

Here is an example of how to use the `Config` Facade:

```php
$databaseConnection = Config::get('database.default');
$appName = Config::get('app.name');
```

In this example, `Config::get('database.default')` will return the default database connection defined in the `database` configuration file, and `Config::get('app.name')` will return the application name defined in the `app` configuration file.

The `Config` Facade also provides typed retrieval methods to ensure that the configuration values are returned in the expected data type. Here are the methods available:

| Method                | Description                                                                 |
|-----------------------|-----------------------------------------------------------------------------|
| `Config::string()`    | Retrieves the configuration value as a string.                              |
| `Config::integer()`   | Retrieves the configuration value as an integer.                            |
| `Config::array()`     | Retrieves the configuration value as an array.                              |
| `Config::float()`     | Retrieves the configuration value as a float.                               |
| `Config::boolean()`   | Retrieves the configuration value as a boolean.                             |

These methods help in maintaining type safety and avoiding type-related errors in your application.

Here is an example of how to use these typed retrieval methods:

```php
$appName = Config::string('app.name');
$maxConnections = Config::integer('database.max_connections');
$services = Config::array('services');
$piValue = Config::float('math.pi');
$isDebugMode = Config::boolean('app.debug');
```

In this example, `Config::string('app.name')` will return the application name as a string, `Config::integer('database.max_connections')` will return the maximum number of database connections as an integer, `Config::array('services')` will return the services configuration as an array, `Config::float('math.pi')` will return the value of pi as a float, and `Config::boolean('app.debug')` will return the debug mode status as a boolean.

## App Debug Mode

To enable or disable debug mode in the Tonka application, you can use the `APP_DEBUG` environment variable defined in your `.env` file. Debug mode is useful during development as it provides detailed error messages and stack traces, which can help in identifying and fixing issues.

### Enabling Debug Mode

To enable debug mode, set the `APP_DEBUG` variable to `true` in your `.env` file:

```
APP_DEBUG=true
```

### Disabling Debug Mode

To disable debug mode, set the `APP_DEBUG` variable to `false` in your `.env` file:

```
APP_DEBUG=false
```

### Accessing Debug Mode in Code

You can access the debug mode status in your code using the `env()` function or the `Config` Facade. Here are examples of both methods:

Using the `env()` function:

```php
$isDebugMode = env('APP_DEBUG', false);
```

Using the `Config` Facade:

```php
$isDebugMode = Config::boolean('app.debug');
```

In both examples, the value of `APP_DEBUG` will be retrieved from the `.env` file, and you can use the `$isDebugMode` variable to conditionally execute code based on whether debug mode is enabled or not.

By properly managing the `APP_DEBUG` environment variable, you can control the level of error reporting and debugging information displayed by your Tonka application, ensuring a smoother development and debugging process.

## Maintenance Mode

Tonka provides maintenance mode through a route service. A route service is a class that extends the `Clicalmani\Foundation\Providers\RouteService` base class and implements the `redirect` method. This method is responsible for handling the redirection of requests when the application is in maintenance mode.

Here is an example of a route service class for maintenance mode:

```php
use Clicalmani\Foundation\Providers\RouteService;

class MaintenanceModeService extends RouteService
{
    /**
     * Constructor
     * 
     * @param \Clicalmani\Routing\Route $route
     */
    public function __construct(protected $route)
    {
        parent::__construct();
    }

    /**
     * Issue a redirect
     * 
     * @return void
     */
    public function redirect()
    {
        /**
         * |-------------------------------------------------------
         * | Maintenence Redirect
         * |-------------------------------------------------------
         * Here we just set a tempory redirect for all request. You
         * may redirect on request base or on user base.
         */
        if ($this->route) $this->route->redirect = [
            503,
            'MAINTENANCE_MODE',
            'The application is currently under maintenance. Please try again later.'
        ];
    }
}
```

In this example, the `MaintenanceModeService` class extends the `RouteService` base class and implements the `redirect` method to return a 503 Service Unavailable response with a maintenance message.

To enable maintenance mode, you can register the `MaintenanceModeService` in your application's route configuration. This will ensure that all incoming requests are redirected to the maintenance mode response when the application is under maintenance.

By using a route service for maintenance mode, Tonka provides a flexible and customizable way to handle maintenance periods, ensuring that users are informed and requests are properly managed during downtime.

By default, Tonka does not provide a built-in maintenance mode. To implement maintenance mode, you need to manually create a route service and register it in the kernel file located in the `bootstrap` folder under the `tps` section.

Next, register your custom route service in the kernel file located in the `bootstrap` folder. Add the service to the `tps` section:

```php
// bootstrap/kernel.php

return [
    'tps' => [
        // Other services...
        MaintenanceModeService::class,
    ],
];
```

!> The request object is made available in a route service through `$this->request` that allow you to base your redirect per request or per user.
