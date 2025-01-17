**Middlewares**

Middleware provide a convenient mechanism for inspecting and filtering HTTP requests entering your application. For example, Tonka includes a middleware that verifies the user of your application is authenticated. If the user is not authenticated, the middleware will redirect the user to your application's login screen. However, if the user is authenticated, the middleware will allow the request to proceed further into the application.

Additional middleware can be written to perform a variety of tasks besides authentication. For example, a setDefaultLocale middleware might add a `locale` parameter to all incoming requests to your application. You may define a variety of middleware to accomplish specific filtering tasks; however, all user-defined middleware are typically located in your application's `app/Http/Middleware` directory.

## Defining middleware
To create a new middleware, use the `make:middleware` console command:

```bash
php tonka make:middleware EnsureUserIsUnderEighting
```
This command will place a new `EnsureUserIsUnderEighting` class within your `app/Http/Middlewares` directory. In this middleware, we will only allow access to the route if user is under eighting. Otherwise, we will redirect the users back to the `/home` URI:

```php
<?php
namespace App\Http\Middlewares;

use Clicalmani\Fundation\Http\Middlewares\Middleware;
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Http\Response\Response;

class EnsureUserIsUnderEighting extends Middleware 
{
    /**
     * Handler
     * 
     * @param Request $request Current request object
     * @param Response $response Http response
     * @param callable $next 
     * @return int|false
     */
    public function handle(Request $request, Response $response, callable $next) : int|false
    {
        /** @var \App\Models\User */
        $user = $request->user();

        if (Carbon::parse($user->birth_date)->age > 18) return $request->redirect()->route('/home');
        
        return $next();
    }

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot() : void
    {
        // ...
    }
}
```
!> All middleware are resolved via the [service container](service-container.md), so you may type-hint any dependencies you need within a middleware's constructor.

## Middleware and response
The `handle()` method of the middleware will receive three parameters: the first parameter is the request, the second parameter is the response and the third parameter is a callback function: 
- The request object will be used to retrieve request parameters or perfom other tasks such as redirecting.
- The response parameter will be used to set the response header by calling methods such as `unauthorized()`, `notFound()`, `forbiden()`, etc ... (see [Clicalmani\Fundation\Http\Response\Response](https://github.com/clicalmani/fundation/blob/main/src/Http/Response/Response.php)).
- The return value of the `next()` function will be use to pass the middleware to the next level.

## Registering middleware
### Global middleware
If you want a middleware to run during every HTTP request to your application, you may append it to the global middleware stack in your application's `app/Http/kernel.php` file:

```php
<?php 
return [

    /**
     * |-------------------------------------------------------------------
     * |                          Web Middlewares
     * |-------------------------------------------------------------------
     * 
     */
    'web' => [
        // ...
    ],

    /**
     * |-------------------------------------------------------------------
     * |                        API Middlewares
     * |-------------------------------------------------------------------
     * 
     */
    'api' => [ 
        'under-18' => App\Http\Middlewares\EnsureUserIsUnderEighting::class,
    ],
];
```

After then you must create a route file for your middleware in the `routes` directory. For exemple you may create file named `under18.php`. That file is known nowhere by Tonka, so you must indicate where to find it by doing so in the `boot()` method of the middleware.

```php
// EnsureUserIsUnderEighting.php

/**
 * Bootstrap
 * 
 * @return void
 */
public function boot() : void
{
    $this->container->inject(fn() => routes_path('/under18.php'));
}
```
As the middleware is registered in the `api` section in `app/Http/kernel.php` file, so you must make sure the method `middleware()` is called in `routes/api.php` file by providing it the middleware name (here 'under-18') as its only argument to register the application routes that will be managed by the middleware.

```php
// routes/api.php

use Clicalmani\Fundation\Routing\Route;

Route::Middleware('under-18');
```
Thus `routes/api.php` may contains other routes, that means the position where `middleware()` method is called in the file is very important. Routes declare before the call will not be part of the middleware, therefore routes declared after the call will be part of the middle as same as routes declared inside the middleware routes file:

```php
// routes/api.php

Route::get('/signin', function() {
    // ..
});

Route::Middleware('under-18');

Route::get('/profile', function() {
    // ...
});
```
`/signin` route will not be managed by `under-18` middleware, because it is declared before the middleware assignement. Therefor `/profile` route will be part of the middleware, because it is declared after the middleware assignment.

!> Let's say we have register another middleware called `over-18`. Then will call the `middleware()` method inside `routes/under-18.php` file passing it `over-18` as argument. Routes declared inside `routes/over-18.php` will be managed by `under-18` middleware. They will still have `over-18` as their unique middleware.

### Assigning middleware to the route
If you would like to assign middleware to specific routes, you may invoke the `middleware()` method when defining the route:

```php
use App\Http\Middlewares\PreventRouteTampering;

Route::get('users/:id/:hash', function() {
    // ...
})->middleware(PreventRouteTampering::class)
```
You may define multiple middlewares to the route by chaining `middleware()` method call.

### Middleware group
If you would like to assign middleware to specific routes, you may invoke the `group()` method by passing an array as its first argument when defining the route:

```php
Route::group(['middleware' => 'tokenizer'], function() {
    // ..
});
```