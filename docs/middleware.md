- [Introduction](middleware.md?id=Introduction)
- [Defining Middleware](middleware.md?id=defining-middleware)
- [Middleware and Responses](middleware.md?id=middleware-and-responses)
- [Registering Middleware](middleware.md?id=registering-middleware)
    - [Global Middleware](middleware.md?id=global-middleware)
    - [Assigning Middleware to Routes](middleware.md?id=assigning-middleware-to-routes)
    - [Middleware Route File](middleware.md?id=middleware-route-file)
    - [Excluding Middleware](middleware.md?id=excluding-middleware)

## Introduction

Middleware provide a convenient mechanism for inspecting and filtering HTTP requests entering your application. For example, **Tonka** includes a middleware that verifies the user of your application is authenticated. If the user is not authenticated, the middleware will redirect the user to your application's login screen. However, if the user is authenticated, the middleware will allow the request to proceed further into the application.

Additional middleware can be written to perform a variety of tasks besides authentication. For example, a `checkAge` middleware might check the age of the user of your application. A variety of middleware are included in **Tonka**, including middleware for authentication and CSRF protection; however, all user-defined middleware are typically located in your application's `app/Http/Middlewares` directory.

## Defining Middleware

To create a new middleware, use the `make:middleware` console command:

```bash
php tonka make:middleware CheckAge
```

This command will place a new `CheckAge` class within your `app/Http/Middlewares` directory. In this middleware, you can define the logic to handle the request. For example, let's verify that the user is over a given age:

```php
<?php

namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Http\Response;
use Clicalmani\Foundation\Http\ResponseInterface;
use Clicalmani\Foundation\Http\RedirectInterface;

class CheckAge extends Middleware
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Foundation\Http\Requests\RequestInterface $request Request object
     * @param \Clicalmani\Foundation\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Foundation\Http\ResponseInterface|\Clicalmani\Foundation\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
    {
        if ($request->age <= 18) {
            return redirect()->route('home');
        }

        // Add the application logic and pass the middleware to the next level

        return $next($request, $response);
    }
}
```

Once the middleware has been defined, it can be attached to a route in your `routes/web.php` file:

```php
Route::get('admin/profile', function () {
    // Only executed if the age is greater than 18...
})->middleware(\App\Http\Middlewares\CheckAge::class);
```

Alternatively, you can assign middleware to a group of routes or globally within your `app/Http/Kernel.php` file. To do so you must register the middleware as a [global middleware](middleware.md?id=global-middleware).

## Middleware and Responses

Of course, a middleware can perform tasks before or after passing the request deeper into the application:

```php
<?php

namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Http\Response;
use Clicalmani\Foundation\Http\ResponseInterface;
use Clicalmani\Foundation\Http\RedirectInterface;

class LogRequest extends Middleware
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Foundation\Http\Requests\RequestInterface $request Request object
     * @param \Clicalmani\Foundation\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Foundation\Http\ResponseInterface|\Clicalmani\Foundation\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
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

If you want a middleware to run during every HTTP request to your application within a route file, you may append it to the global middleware stack in your application's `app/Http/kernel.php` file:

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

To ensure that middleware is applied to all routes within the file, you should call the middleware at the beginning of your route file. Any routes defined before the middleware call will not be handled by that middleware. For example:

```php
// routes/web.php

// Routes not handled by the middleware
Route::get('/', function () {
    // Public home page
});

// Call middleware
Route::middleware('check.age');

// Routes handled by the middleware
Route::get('admin/profile', function () {
    // Only accessible if the age is greater than 18...
});
```

In this example, the `/` route is accessible to everyone, while the `admin/profile` route is protected by the `check.age` middleware. This approach allows you to control exactly which routes are affected by specific middleware.

### Assigning Middleware to Routes

If you would like to assign middleware to specific routes, you may invoke the middleware method when defining the route:

```php
Route::get('admin/profile', function () {
    // Only executed if the age is greater than 18...
})->middleware('check.age');
```

You can also assign multiple middleware to the route by passing an array of middleware:

```php
Route::get('admin/profile', function () {
    // Only executed if the age is greater than 18 and the request is logged...
})->middleware(['check.age', 'log.request']);
```

This will ensure that the `CheckAge` and `LogRequest` middleware are executed in the specified order for the route.

### Middleware Route File

To keep your route definitions clean and organized, you can define middleware in a separate route file. For example, you can create a `routes/auth.php` file and define your routes with `auth` middleware there:

```php
// routes/web.php

Route::middleware('auth');
```

```php
// routes/auth.php

Route::get('admin/profile', function () {
    // Show profile
});

Route::get('admin/settings', function () {
    // Show settings
});
```

The `routes/auth.php` needs to be initialized in the boot method of the middleware:

```php
/**
     * Bootstrap
     * 
    * @return void
    */
public function boot() : void
{
    $this->include('auth');
}
```

This approach helps in maintaining a clean and manageable route definition structure.

### Excluding Middleware

When assigning middleware to a group of routes, you may occasionally need to prevent the middleware from being applied to an individual route within the group. You may accomplish this using the `withoutMiddleware` method:

```php
Route::group(['middleware' => 'check.age|log.request'], function () {
        Route::get('/profile', function () {
            // This route has both middleware applied...
        });

        Route::get('/settings', function () {
            // This route will not have the CheckAge middleware applied...
        })->withoutMiddleware('log.request');
})->prefix('admin');
```

In this example, the `admin/settings` route will not have the `CheckAge` middleware applied, but it will still have the `LogRequest` middleware applied.