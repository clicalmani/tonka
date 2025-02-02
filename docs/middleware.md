## Introduction

Middleware provide a convenient mechanism for inspecting and filtering HTTP requests entering your application. For example, Tonka includes a middleware that verifies the user of your application is authenticated. If the user is not authenticated, the middleware will redirect the user to your application's login screen. However, if the user is authenticated, the middleware will allow the request to proceed further into the application.

Additional middleware can be written to perform a variety of tasks besides authentication. For example, a `checkAge` middleware might check the age of the user of your application. A variety of middleware are included in Tonka, including middleware for authentication and CSRF protection; however, all user-defined middleware are typically located in your application's `app/Http/Middlewares` directory.

## Defining Middleware

To create a new middleware, use the make:middleware console command:

```bash
php tonka make:middleware CheckAge
```

This command will place a new `CheckAge` class within your `app/Http/Middlewares` directory. In this middleware, you can define the logic to handle the request. For example, let's verify that the user is over a given age:

```php
<?php

namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Http\Response\Response;

class CheckAge extends Middleware
{
    /**
     * Handle the incoming request.
     * 
     * @param Request $request Current request object
     * @param Response $response Http response
     * @param callable $next 
     * @return int|false
     */
    public function handle(Request $request, Response $response, callable $next) : int|false
    {
        if ($request->age <= 18) {
            return $request->redirect()->route('home');
        }

        return $next($request);
    }
}
```

Once the middleware has been defined, it can be attached to a route in your `routes/web.php` file:

```php
Route::get('admin/profile', function () {
    // Only executed if the age is greater than 18...
})->middleware(\App\Http\Middlewares\CheckAge::class);
```

Alternatively, you can assign middleware to a group of routes or globally within your `app/Http/Kernel.php` file.

## Middleware and Responses

Of course, a middleware can perform tasks before or after passing the request deeper into the application:

```php
<?php

namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Http\Response\Response;

class LogRequest extends Middleware
{
    /**
     * Handle the incoming request.
     * 
     * @param Request $request Current request object
     * @param Response $response Http response
     * @param callable $next 
     * @return mixed
     */
    public function handle(Request $request, Response $response, callable $next)
    {
        // Perform action before passing the request deeper into the application
        $this->log($request);

        // Pass the request deeper into the application
        $response = $next($request);

        // Perform action after the request has been handled by the application
        $this->logResponse($response);

        return $response;
    }

    protected function log(Request $request)
    {
        // Log request details
    }

    protected function logResponse(Response $response)
    {
        // Log response details
    }
}
```

In this example, the `LogRequest` middleware logs the request details before passing the request deeper into the application and logs the response details after the request has been handled by the application. This allows you to perform actions both before and after the request is processed.

## Registering Middleware

### Global Middleware

If you want a middleware to run during every HTTP request to your application, you may append it to the global middleware stack in your application's `app/Http/kernel.php` file:

```php
/**
     * |-------------------------------------------------------------------
     * |                          Web Middlewares
     * |-------------------------------------------------------------------
     * 
     */
    'web' => [
        'ckeck.age' => \App\Http\Middlewares\CheckAge::class,
        'log.request' => \App\Http\Middlewares\LogRequest::class,
    ],

    /**
     * |-------------------------------------------------------------------
     * |                        API Middlewares
     * |-------------------------------------------------------------------
     * 
     */
    'api' => [
        // ...
    ],
```

This will ensure that the `CheckAge` and `LogRequest` middleware are executed for every request to your application.

### Assigning Middleware to Routes

If you would like to assign middleware to specific routes, you may invoke the middleware method when defining the route:

```php
Route::get('admin/profile', function () {
    // Only executed if the age is greater than 18...
})->middleware(\App\Http\Middlewares\CheckAge::class);
```

You can also assign multiple middleware to the route by passing an array of middleware:

```php
Route::get('admin/profile', function () {
    // Only executed if the age is greater than 18 and the request is logged...
})->middleware([\App\Http\Middlewares\CheckAge::class, \App\Http\Middlewares\LogRequest::class]);
```

This will ensure that the `CheckAge` and `LogRequest` middleware are executed in the specified order for the route.

### Excluding Middleware

When assigning middleware to a group of routes, you may occasionally need to prevent the middleware from being applied to an individual route within the group. You may accomplish this using the `withoutMiddleware` method:

```php
Route::group(['middleware' => \App\Http\Middlewares\CheckAge::class . '|' . \App\Http\Middlewares\LogRequest::class], function () {
        Route::get('/profile', function () {
            // This route has both middleware applied...
        });

        Route::get('/settings', function () {
            // This route will not have the CheckAge middleware applied...
        })->withoutMiddleware(\App\Http\Middlewares\CheckAge::class);
})->prefix('admin');
```

In this example, the `admin/settings` route will not have the `CheckAge` middleware applied, but it will still have the `LogRequest` middleware applied.