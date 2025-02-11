<h1>Routing</h1>

- [Basic Routing](routing.md?id=basic-routing)
- [The Default Routes Files](routing.md?id=the-default-routes-files)
    - [Web Routes](routing.md?id=web-routes)
    - [API Routes](routing.md?id=api-routes)
- [Available Router Methods](routing.md?id=available-router-methods)
- [Dependency Injection](routing.md?id=dependency-injection)
- [CSRF Protection](routing.md?id=csrf-protection)
- [Route Parameters](routing.md?id=route-parameters)
    - [Required Parameters](routing.md?id=required-parameters)
    - [Optional Parameters](routing.md?id=optional-parameters)
    - [Parameter Validation](routing.md?id=parameter-validation)
    - [Prevent Route Tempering](routing.md?id=prevent-route-tempering)
- [Advance Routing](routing.md?id=advance-routing)
- [Named Routes](routing.md?id=named-routes)
- [Inspecting the Current Route](routing.md?id=inspecting-the-current-route)
- [Route Groups](routing.md?id=route-groups)
    - [Middlewares](routing.md?id=middlewares)
    - [Controllers](routing.md?id=controllers)
- [Validation](routing.md?id=validation)
    - [Validating Route Parameters](routing.md?id=validating-route-parameters)
    - [Validating Request Data](routing.md?id=validating-request-data)
    - [Custom Validation Rules](routing.md?id=custom-validation-rules)

## Basic Routing

To write the most basic Tonka route, you can use the `Route::get` method to define a route that responds to a GET request. Here's an example:

```php
Route::get('/basic', function() {
    return 'This is a basic route!';
});
```

## The Default Routes Files

### Web Routes

All Tonka routes are defined in your route files, which are located in the `routes` directory. These files are automatically loaded by Tonka using the configuration specified in your application's `bootstrap/app.php` file and the `App\Providers\RouteServiceProvider` file. The `routes/web.php` file defines routes that are for your web interface. These routes provides features like session state and CSRF protection.

### API Routes

Tonka by default provides the `api.php` file to store API routes, but you can also create other route files. Each route file must be linked to a [global middleware](). The default API middleware is the `App\Http\Middlewares\Authenticate` class. The API default middleware is registered globally as `api` and automatically called on each API request.

The routes in `routes/api.php` are stateless and are assigned to the `api` middleware group. Additionally, the `/api` URI prefix is automatically applied to these routes, so you do not need to manually apply it to every route in the file. You may change the prefix by defining it in `App\Providers\RouteServiceProvider` file:

```php
<?php
namespace App\Providers;

use Clicalmani\Foundation\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * API Routes prefix
     * 
     * @var string 
     */
    protected $api_prefix = 'api';
}
```

## Available Router Methods

Tonka PHP Framework's routing system allows you to define routes that respond to all HTTP verbs using methods such as `Route::get`, `Route::post`, `Route::put`, `Route::patch`, `Route::delete`, and `Route::options`. Each method corresponds to an HTTP verb and takes a URI and a closure or controller action as parameters. 

For example, to define a route that responds to a `POST` request, you can use:

```php
Route::post('/example', function() { 
    return 'This is a POST route!'; 
});
```

This flexibility enables you to handle different types of requests and build a robust API or web application.

Sometimes you may need to register a route that responds to multiple HTTP verbs. You may do so using the `match` method:

```php
Route::match(['get', 'post'], '/', function () {
    // ...
});
```

## Dependency Injection

You may type-hint any dependencies required by your route in the route callback signature, and Tonka will automatically resolve them from the [service container](). Here's an example:

```php
<?php
use App\Models\User;

Route::get('/user/{id}', function(int $id) {
    $user = User::find($id);
    return response()->json($user);
});
```

In this example, the `User` dependency is automatically resolved and injected into the route callback.

## CSRF Protection

Remember, any HTML forms pointing to `POST`, `PUT`, `PATCH`, or `DELETE` routes that are defined in the `web` routes file should include a CSRF token field. Otherwise, the request will be rejected. You can read more about CSRF protection in the [CSRF documentation]():

```php
<form method="POST" action="/profile">
    @csrf
    ...
</form>
```

## Route Parameters

In Tonka route parameters are dynamic and allows you to create variable parameter in your route URI. This is useful when you need to capture a segment of the URI that can vary. You can define a dynamic route parameter by placing a double dots `(:)` before the parameter name. Here's an example:

```php
Route::get('/:entity/:id', function (string $entity, int $id) {
    return "Entity: $entity, ID: $id";
});
```

In this example, the `:entity` and `:id` parameters are dynamic and can match any value. When the route is accessed, the actual values will be passed to the route callback.

Tonka route parameters are specified by placing two dots `(:)` before the parameter name. A parameter name can only contain ASCII characters without spaces. You can use hyphens `(-)` and underscores `(_)` to concatenate multiple words:

```php
Route::get('/user/:user_id', function(int $user_id) {
    return 'User ID: ' . $user_id;
});
```

!> By default, the parameter identifier in Tonka is `(:)`. However, you may change it and use any other character by specifying it in `App\Providers\RouteServiceProvider`. This allows you to customize the route parameter syntax according to your preferences.

### Required Parameters

Sometimes you will need to capture segments of the URI within your route. For example, you may need to capture a user's ID from the URL. You may do so by defining route parameters:

```php
Route::get('/user/:id', function (int $id) {
    return "User ID: $id";
});
```

You may define as many route parameters as required by your route:

```php
Route::get('/posts/:post/comments/:comment', function (int $postId, int $commentId) {
    // ...
});
```

Route parameters with same root are treated the same for the same HTTP verb. The only difference between them is the values they hold. This allows Tonka to provide you with dynamic routes within your application:

```php
<?php
Route::get('/users/:user_id', function(int $user_id) {
    return 'User ID: ' . $user_id;
});
```

The `:user_id` parameter is treated as a placeholder that will be replaced by the actual user ID value when the route is accessed.

Similarly, in this route:

```php
<?php
Route::get('/users/:post_id', function (int $postId) {
    // ...
});
```

`:post_id` is a placeholder that will be replaced by the actual post ID value at runtime.

So in your environment, both the two routes `/users/:user_id` and `/users/post_id` in the exemples above will be treated as the same and resolting in route conflict, meaning you will receive a duplicate route warning.

Don't panic relax! :blush: Tonka is highly based on validation, route parameters, entity attributes, and request parameters are subjects to validation. We will cover validation in the [Validation]() section.

To resolve the dilemma in the exemples above we need to use validators on the specified parameters to differenciate them.

### Optional Parameters

Occasionally you may need to specify a route parameter that may not always be present in the URI. You may do so by placing a `?` mark before the parameter prefix. Make sure to put the parameter at the end:

```php
Route::get('/user/?:name', function (?string $name = null) {
    return $name;
});
```

!> Optional parameters should be placed at the end of stack to avoid conflicting with other routes.

### Parameter Validation

You can also apply validation constraints to your route parameters to ensure they meet certain criteria:

```php
Route::get('/:entity/:id', function (string $entity, int $id) {
    return "Entity: $entity, ID: $id";
})->where('entity', 'required|string')->where('id', 'required|number');
```

This ensures that the `entity` parameter is a required string and the `id` parameter is a required number.

Tonka provides several built-in validators, including `email` validator, `id` validator, `number` validator, `datetime` validator, `enum` validator... These validators can be used anywhere in your application where validation is needed to enhance the reliability and security of your application. You can create your own custom validation and use it on your route or anywhere in your code. We will cover [validation](routing.md?id=validation) later.

```php
Route::get('/user/:id', function (int $id) {
    return "User ID $id";
})->where('id', 'required|number');

Route::get('/user/:id', function (int $id) {
    return "User ID $id";
})->where('id', 'required|id|model:user');

Route::get('/user/:email', function (string $email) {
    return "User Email $email";
})->where('email', 'required|email');

Route::get('/post/:category', function (string $category) {
    return "Post category $category";
})->where('category', 'required|enum|list:football,handball,bascketball');

Route::get('/post/:date', function (string $date) {
    return "Post Date $date";
})->where('date', 'required|datetime|format:Y-m-d');
```

If the incoming request does not match the validation constraints, a 404 HTTP response will be returned.

### Prevent Route Tempering

To prevent route tempering, you can use route constraints and middleware to ensure that the incoming requests match the expected patterns and criteria. Here is an example of how to prevent route tempering:

```php
use App\Http\Middlewares\PreventRouteTempering;

Route::get('/user/:id/:hash', function (int $id) {
    return "User ID $id";
})->where('id', 'required|id|model:user')->middleware(PreventRouteTempering::class);
```

This line applies the `PreventRouteTempering` middleware to the route. The middleware will verify the `hash` parameter before the route's callback function, allowing it to perform checks and validations to prevent route tampering. The `hash` parameter can be generated by `create_parameters_hash` helper function. Here is an example:

```php
Route::get('/users/:id', function(int $id) {
    return redirect()->route('/user/:id/:hash', $id, create_parameters_hash(['id' => $id]));
});
```

!> The hash parameter name can be customize in `config/hashing.php` by specifying a different value for the `hash_parameter` entry.

### Global Constraints

If you would like a route parameter to always be constrained by a given validation, you may use the `validate` method. You should define these validations in the `register` method of your application's `App\Providers\RouteServiceProvider` class:

```php
use Clicalmani\Foundation\Routing\Route;
 
/**
 * Bootstrap any application services.
 */
public function register(): void
{
    Route::validate('id', 'required|id|model:user');
}
```

Once the validation constraint has been defined, it is automatically applied to all routes using that parameter name:

```php
use App\Models\User;

Route::get('/user/:id', function (int $id) {
    $user = User::find($id);
});
```

## Advance Routing

Tonka allows you to create your own route processor if you have some particular routes in your application that you want to handle in a specific way. To create a custom route processor, you need to define a class that implements the `Clicalmani\Routing\BuilderInterface` and extends the `Clicalmani\Routing\Builder` class. After then you add it into the list of route builders in `config/routing.php` file.

Here's an example of a custom route processor:

```php
<?php

namespace App\Routing;

use Clicalmani\Routing\Builder;
use Clicalmani\Routing\BuilderInterface;

class CustomRouteProcessor extends Builder implements BuilderInterface
{
    /**
     * Create a new route.
     * 
     * @param string $uri Route uri
     * @return \Clicalmani\Routing\Route
     */
    public function create(string $uri) : \Clicalmani\Routing\Route
    {
        $route = new \Clicalmani\Routing\Route;
        $route->setUri($uri);
        return $route;
    }

    /**
     * Match candidate routes.
     * 
     * @param string $verb
     * @return \Clicalmani\Routing\Route[] 
     */
    public function matches(string $verb) : array
    {
        /**
         * @var array
         */
        $candidates = [];

        /** @var \Clicalmani\Routing\Route $route */
        foreach (\Clicalmani\Routing\Memory::getRoutesByVerb($verb) as $route) {
            if ($route->uri !== '/custom') continue;

            if ($this->isBuilt($route)) 
                throw new \Clicalmani\Routing\Exceptions\DuplicateRouteException($route);

            $candidates[] = $route;
        }
        
        return $candidates;
    }

    /**
     * Locate the current route in the candidate routes list.
     * 
     * @param \Clicalmani\Routing\Route[] $matches
     * @return \Clicalmani\Routing\Route|null
     */
    public function locate(array $matches) : \Clicalmani\Routing\Route|null
    {
        return array_pop($matches);
    }

    /**
     * Build the requested route. 
     * 
     * @return \Clicalmani\Routing\Route|null
     */
    public function getRoute() : \Clicalmani\Routing\Route|null
    {
        return $this->locate(
            $this->matches( 
                \Clicalmani\Foundation\Routing\Route::getClientVerb()
            ) 
        );
    }
}
```

Next, register the custom route processor in your `config/routing`:

```php
<?php

/*
 |--------------------------------------------------------------------------
 | Available Route Builders
 |--------------------------------------------------------------------------
 |
 | Here you may specify the route builders that should be used when building
 | routes for your application. you remain free to add any builder you want.
 |
 */

'builders' => [
    \Clicalmani\Routing\Factory\RegExBuilder::class,
]
```

With this setup, the custom route processor will handle the `/custom` route and apply any custom logic defined in the `process` method. This allows you to extend the routing capabilities of Tonka to suit your application's specific needs.

## Named Routes

Named routes allow the convenient generation of URLs or redirects for specific routes. You may specify a name for a route by chaining the `name` method onto the route definition:

```php
Route::get('/user/profile', function () {
    // ...
})->name('profile');
```

Once you have assigned a name to a route, you may use the route's name when generating URLs or redirects via the `route` function:

```php
$url = route('profile');
```

You may also generate redirects to named routes:

```php
return redirect()->route('profile');
```

Named routes are particularly useful when you need to generate URLs or redirects for routes that may change their URI structure in the future. By using named routes, you can ensure that your URL generation logic remains consistent even if the underlying route definitions change.

!> Route names should always be unique.

## Inspecting the Current Route

If you would like to determine if the current request was routed to a given named route, you may use the `named` method on a Route instance. For example, you may check the current route name from a route middleware:

```php
use Clicalmani\Routing\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        // ...
    })->name('dashboard');
});

Route::get('/current-route', function () {
    if (Route::current()->named('dashboard')) {
        return 'This is the dashboard route';
    }

    return 'This is not the dashboard route';
});
```

In this example, the `Route::current()->named('dashboard')` method is used to check if the current route is named `dashboard`. If it is, a specific message is returned. This can be useful for applying logic based on the current route within your application.

you may also check named route in your custom middleware:

```php
use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Http\Response\Response;

class CustomMiddleware extends Middleware 
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
        if ($request->route()->named('profile')) {
            // ...
        }
    
        return $next($request);
    }
}
```

## Route Groups

Route groups allow you to share route attributes, such as middleware, across a large number of routes without needing to define those attributes on each individual route.

Nested groups attempt to intelligently "merge" attributes with their parent group. Middleware and where conditions are merged while names and prefixes are appended. Namespace delimiters and slashes in URI prefixes are automatically added where appropriate.

Here's an example of defining a route group with shared middleware:

```php
Route::group(['prefix' => 'admin'], function() {
    Route::get('/users', function () {
        // URL: /admin/users
    });

    Route::get('/settings', function () {
        // URL: /admin/settings
    });
});
```

In this example, both the `/users` and `/settings` routes will have the `/admin` prefix.

You can also define route groups with shared middlewares:

```php
Route::group(['middleware' => 'auth|localize'], function () {
    Route::get('/users', function () {
        // URL: /admin/users
    });

    Route::get('/settings', function () {
        // URL: /admin/settings
    });
})->prefix('admin');
```

In this example, both routes will have the `/admin` prefix and use the `auth` and `localize` middlewares.

Route groups can also be nested:

```php
Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'admin'], function() {
        Route::get('/users', function () {
            // URL: /admin/users
        });

        Route::get('/settings', function () {
            // URL: /admin/settings
        });
    });
});
```

In this example, the routes will use the `auth` middleware and have the `/admin` prefix.

Route groups provide a convenient way to organize and manage your routes, making it easier to apply common attributes to multiple routes.

### Middlewares

To assign middleware to all routes within a group, you may use the `middleware` method. Middleware are executed in the order they are listed:

```php
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        // Uses 'first' and 'second' middleware
    });

    Route::get('/settings', function () {
        // Uses 'first' and 'second' middleware
    });
})->prefix('users');
```

In this example, both the `/profile` and `/settings` routes will use the `auth` middleware and have `users` prefix.

You can also use the `group` method to apply middleware to a group of routes:

```php
Route::group(['middleware' => 'auth|localize'], function () {
    Route::get('/profile', function () {
        // Uses 'first' and 'second' middleware
    });

    Route::get('/settings', function () {
        // Uses 'first' and 'second' middleware
    });
})->prefix('admin');
```

In this example, both the `/profile` and `/settings` routes will use the `auth` and `localize` middleware and have `admin` prefix.

Middleware can be applied to route groups to ensure that all routes within the group share the same middleware, making it easier to manage and maintain your application's routing logic.

### Controllers

If a group of routes all utilize the same controller, you may use the `controller` method to define the common controller for all of the routes within the group. Then, when defining the routes, you only need to provide the controller method that they invoke:

```php
Route::controller(UserController::class)->group(function () {
    Route::get('/profile', 'show');
    Route::post('/profile', 'update');
});
```

In this example, both routes will use the `UserController` class. The `/profile` route will invoke the `show` method for GET requests and the `update` method for POST requests.

This approach helps to keep your route definitions clean and organized by grouping routes that share the same controller.

## Validation

Tonka provides a robust validation system that allows you to validate incoming request data. You can use the `where` method to apply validation rules to your route parameters. Here's an example of how to use validation in your routes:

### Validating Route Parameters

You can apply validation rules directly to your route parameters using the `where` method:

```php
Route::get('/user/:id', function (int $id) {
    return "User ID $id";
})->where('id', 'required|number|min:1');
```

In this example, the `id` parameter must be a required number. If the validation fails, a 404 HTTP response will be returned.

### Validating Request Data

To validate request data, you can use the `validate` method within your route callback or controller method. Here's an example:

```php
use Clicalmani\Foundation\Http\Requests\Request;

Route::post('/user', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed:1',
    ]);

    // The incoming request is valid...
    // You may proceed with your logic here...
});
```

In this example, the `validate` method is used to apply validation rules to the incoming request data. If the validation fails, a 422 HTTP response will be returned with the validation errors.

### Custom Validation Rules

You can create custom validation rules by extending the `Clicalmani\Validation\Validator` class. Here's an example of a custom validation rule:

```php
<?php

namespace App\Validators;

use Clicalmani\Validation\Validator;

class PhoneNumberValidator extends Validator
{
    /**
     * Validator argument
     * 
     * @var string
     */
    protected string $argument = 'phone';

    /**
     * Validator options
     * 
     * @return array
     */
    public function options() : array
    {
        return [
            'pattern' => [
                'required' => true,
                'type' => 'string'
            ]
        ];
    }

    /**
     * Validate the attribute.
     *
     * @param mixed &$value
     * @param array $options
     * @return bool
     */
    public function validate(&$value, array $options): bool
    {
        // Custom validation logic for phone numbers
        return preg_match('/^' . $this->options['pattern'] . '$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return sprintf("The %s field must be a valid phone number.", $this->parameter);
    }
}
```

You can then use the custom validation rule in your validation logic:

```php
Route::post('/user', function (Request $request) {
    // ...
})->where('phone', 'required|phone|pattern:[0-9]{8}');
```