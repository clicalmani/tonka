**<h1>Controllers</h1>**

## Introduction
Instead of defining all of your request handling logic as closures in your routing files, you may want to organize this behavior using "controller" classes. Controllers can group related request handling logic into a single class. For example, a `UserController` class can handle all incoming user-related requests, including viewing, creating, updating, and deleting users. By default, controllers are stored in the `app/Http/Controllers` directory.

## Writing Controllers
### Basic Controllers
To quickly generate a new controller, you can run `make:controller` Console command. By default, all controllers in your application are stored in the `app/Http/Controllers` directory:

```bash
php tonka make:controller UserController
```
Let's take a look at an example of a basic controller. A controller may have any number of public methods which will respond to incoming HTTP requests:

```php
<?php 
namespace App\Http\Controllers;

use Clicalmani\Fundation\Http\Requests\RequestController as Controller;
use Clicalmani\Fundation\Http\Response\Response;
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Test\HasTest;

class UserController extends Controller
{
    use HasTest;
    
    /**
     * Show the profile for a given user.
     */
    public function show(Request $request, int $id): View
    {
        return view('user.profile', [
            'user' => User::findOrFail($id)
        ]);
    }
}
```
Once you have written a controller class and method, you may define a route to the controller method like so:

```php
use App\Http\Controllers\UserController;
 
Route::get('/user/:id', [UserController::class, 'show']);
```
!> Controllers are not required to extend a base class. However, it is sometimes convenient to extend a base controller class that contains methods that should be shared across all of your controllers.

### Single Action Controllers
If a controller action is particularly complex, you might find it convenient to dedicate an entire controller class to that single action. To accomplish this, you may define a single __invoke method within the controller:

```php
<?php
 
namespace App\Http\Controllers;
 
class ProvisionServer extends Controller
{
    /**
     * Provision a new web server.
     */
    public function __invoke()
    {
        // ...
    }
}
```

When registering routes for single action controllers, you do not need to specify a controller method. Instead, you may simply pass the name of the controller to the router:

```php
use App\Http\Controllers\ProvisionServer;
 
Route::post('/server', ProvisionServer::class);
```

## Controller Middleware
[Middleware](routing/middlewares.md) may be assigned to the controller's routes in your route files:

```php
Route::get('/profile', [UserController::class, 'show'])->middleware('auth');
```
You may also define controller middleware as inline middleware class:

```php
use App\Http\Middlewares\PreventRouteTampering;

Route::get('/users/:id/:hash', [UserController::class, 'show'])->middleware(PreventRouteTampering::class);
```
## Resource Controllers
If you think of each DBQuery model in your application as a "resource", it is typical to perform the same sets of actions against each resource in your application. For example, imagine your application contains a Photo model and a Movie model. It is likely that users can create, read, update, or delete these resources.

Because of this common use case, Tonka resource routing assigns the typical create, read, update, and delete ("CRUD") routes to a controller with a single line of code. To get started, we can use the `make:controller` Console command's --resource option to quickly create a controller to handle these actions:

```bash
php tonka make:controller PhotoController --resource=Photo
```
This command will generate a controller at `app/Http/Controllers/PhotoController.php`. The controller will contain a method for each of the available resource operations. Next, you may register a resource route that points to the controller:

```php
use App\Http\Controllers\PhotoController;
 
Route::resource('photos', PhotoController::class);
```
**<h2>Actions Handled by Resource Controllers</h2>**

|  Verb  |   URI   | Action | Route Name |
|--------|---------|--------| -----------|
| GET    | `/photos` | `index`  | `photos.index` |
| GET | `/photos/create` | `create` | `photos.create` |
| POST | `/photos` | `store` | `photos.store` |
| GET | `/photos/:photo` | `show` | `photos.show` |
| GET | `/photos/:photo/edit` | `edit` | `photos.edit` |
| PUT/PATCH | `/photos/:photo` | `update` | `photos.update` |
| DELETE | `/photos` | `destroy` | `photos.destroy` |

**<h2>Customizing Missing Model Behavior</h2>**
Typically, a 404 HTTP response will be generated if an implicitly bound resource model is not found. However, you may customize this behavior by calling the `missing()` method when defining your resource route. The `missing()` method accepts a closure that will be invoked if an implicitly bound model can not be found for any of the resource's routes:

```php
use App\Http\Controllers\PhotoController;
 
Route::resource('photos', PhotoController::class)
        ->missing(fn() => "The specified resource could not been found.");
```
**<h2>Partial Resource Routes</h2>**
When declaring a resource route, you may specify a subset of actions the controller should handle instead of the full

```php
use App\Http\Controllers\PhotoController;
 
Route::resource('photos', PhotoController::class)->only([
    'index', 'show'
]);
 
Route::resource('photos', PhotoController::class)->except([
    'create', 'store', 'update', 'destroy'
]);
```
**<h2>API Resource Routes</h2>**
When declaring resource routes that will be consumed by APIs, you will commonly want to exclude routes that present HTML templates such as create and edit. For convenience, you may use the `apiResource()` method to automatically exclude these two routes:

```php
use App\Http\Controllers\PhotoController;
 
Route::apiResource('photos', PhotoController::class);
```

To quickly generate an API resource controller that does not include the create or edit methods, use the `--api `switch when executing the `make:controller` command:

```bash
php tonka make:controller PhotoController --api
```

## Dependency Injection and Controllers
The service container is used to resolve all controllers. As a result, you are able to type-hint any dependencies your controller may need in its methods. The declared dependencies will automatically be resolved and injected into the controller instance:

```php
<?php
 
namespace App\Http\Controllers;

use Clicalmani\Fundation\Http\Requests\Request;
use App\Models\User;
 
class UserController extends Controller
{
    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        // Store the user...
        $user = new User;
        $user->swap();
        $user->save();
 
        return response()->redirect()->route('/users');
    }
}
```
If your controller method is also expecting input from a route parameter, list your route arguments after your other dependencies. For example, if your route is defined like so:

```php
use App\Http\Controllers\UserController;
 
Route::put('/user/:id', [UserController::class, 'update']);
```

You may still type-hint the `Clicalmani\Fundation\Http\Requests\Request` and access your id parameter by defining your controller method as follows:

```php
<?php
 
namespace App\Http\Controllers;
 
use Clicalmani\Fundation\Http\Requests\Request;
 
class UserController extends Controller
{
    /**
     * Update the given user.
     */
    public function update(Request $request, int $id)
    {
        // Update the user...
        $user = User::find($id);
        $user->swap();
        $user->save();
 
        return response()->redirect()->route('/users');
    }
}
```
## Input Validation

### Introduction
We saw earlier how to validate routes in routes files by calling the `where()` method. By doing so we where able to validate route parameters. Here we will see how to validate input data in general:

### Declaring Input Validator
Input validator can be declare on any controller method by calling `AsValidator()` as method attribute:

```php
<?php
 
namespace App\Http\Controllers;
 
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Validation\AsValidator;
 
class UserController extends Controller
{
    /**
     * Update the given user.
     */
    #[AsValidator(
        id: 'required|id|model:user'
    )]
    public function update(Request $request, int $id)
    {
        // Update the user...
        $user = User::find($id);
        $user->swap();
        $user->save();
 
        return response()->redirect()->route('/users');
    }
}
```
When executing the route, Tonka will validate the `id` parameter requirement before calling the `update()` method. If validation fails, request will also fail by providing a `404` header status.

!> You can specify as many inputs as you can in a single validator.

### Custom Validators
A custom validator can be created by running `make:validator` Console command:

```bash
php tonka make:validator CustomValidator --argument=<argument>
```
We will take the exemple of the built-in `IDValidator` to show you how to create your own validator:

```bash
php tonka make:validator IDValidator --argument=id
```
`IDValidator` is the validator class name and `id` its argument.

After running the command, `IDValidator.php` file will be created in `app/Http/Validators` folder:

```php
<?php
namespace App\Http\Validators;

use Clicalmani\Fundation\Validation\InputValidator as Validator;

class IDValidator extends Validator
{
    /**
     * Validator argument
     * 
     * @var string
     */
    protected string $argument = "id";

    /**
     * Validator options
     * 
     * @return array
     */
    public function options() : array
    {
        return [
            // options
        ];
    }

    /**
     * Validate input
     * 
     * @param mixed &$value Input value
     * @param ?array $options Validator options
     * @return bool
     */
    public function validate(mixed &$value, ?array $options = [] ) : bool
    {
        // ...
    }
}
```
In `App\Http\Validators\IDValidator` class we have two methods: `options()` method and `validate()` method.

`options()` method must return the argument options. And `validate()` method must contain the validator implementation.

```php
<?php
namespace Clicalmani\Fundation\Validation\Validators;

use Clicalmani\Fundation\Validation\InputValidator;

class IDValidator extends InputValidator
{
    protected string $argument = 'id';

    public function options() : array
    {
        return [
            'model' => [
                'required' => true,
                'type' => 'string',
                'function' => fn(string $model) => collection(explode('_', $model))->map(fn(string $part) => ucfirst($part))->join('')
            ],
            'primary' => [
                'required' => false,
                'type' => 'string',
                'function' => function(string $primary) {
                    if ( strpos($primary, ',') ) $primary = explode(',', $primary);
                    return $primary;
                }
            ]
        ];
    }

    public function validate(mixed &$value, ?array $options = []) : bool
    {
        $model = trim("\\App\\Models\\" . $options['model']);
        /** @var \Clicalmani\Database\Factory\Models\Model */
        $instance = $model::find($value);
        $primaryKey = @ $options['primary'] ? $options['primary']: $instance->getKey();
        
        if ( class_exists($model) ) {
            
            if ( is_array($primaryKey) ) $value = explode(',', $value);
            
            if ('null' === json_encode($model::find($value))) return false;
            
            return true;  
        }

        return false;
    }
}
```
**<h2>Validator Options Attributes Specification</h2>**

| Attribute | Description | Values |  
| --------- | ----------- | ------- |
| `required` | Specify whether the attribute is required | `true` and `false` |
| `type` | Specify the attribute type | `string, integer, array, boolean, object` |
| `function` | A callback function to receive the option value and returns the parsed value | 
| `keys` | If attribute value must be an associative array `keys` holds the array keys. |
| `validator` | Another callback function that receive the attribute value and returns `true` if a valid value is passed, `false` otherwise. |

Validators are made for code usability and they also allows you to separate the logic from the code.

Let's assume we are building an online lotely application that ask user to choose 5 lucky numbers. Each number must be a 2 digits number greater than 10 and less than 100. For that purpose we use an input field to collect all the lucky numbers at once. To validate such a field, we decided to create a special validator called `LuckyNumbersValidator`:

```bash
php tonka make:validator LuckyNumberValidator --argument=lucky[]
```

```php
<?php
namespace App\Http\Validators;

use Clicalmani\Fundation\Validation\Validators\ObjectsValidator as Validator;

class LuckyNumbersValidator extends Validator
{
    /**
     * Validator argument
     * 
     * @var string
     */
    protected string $argument = "lucky[]";

    /**
     * Validator options
     * 
     * @return array
     */
    public function options() : array
    {
        $options = parent::options();
        unset($options['translate']); // We don't need to translate

        $options['join'] = [
            'required' => false,
            'type' => 'bool'
        ];

        return $options;
    }

    /**
     * Validate input
     * 
     * @param mixed &$value Input value
     * @param ?array $options Validator options
     * @return bool
     */
    public function validate(mixed &$value, ?array $options = [] ) : bool
    {
        parent::validate($value, $options);
        
        $value = $this->parseArray($value); // Make sure we are receiving an array
        
        foreach ($value as $index => $num) {

            // Check value attribute
            if (!isset($num->value) || !is_numeric($num->value)) return false;

            // Check order attribute
            if (isset($num->order)) {

                if (!is_numeric($num->order)) return false;

                $num->order = $this->parseInt($num->order);
            }

            $num->value = $this->parseFloat($num->value);

            $value[$index] = $num->value < 10 ? 10: $num->value; // 10 must be the lowest value
            $value[$index] = $num->value > 99 ? 99: $num->value; // 99 must be the highest value
        }

        if (@$options['join']) $value = join(',', $value); // Optionally join numbers
        
        return true;
    }
}
```
After implementing our custom validator, the last step will be to register it in `app/Http/kernel.php` file:

```php
return [

    /**
     * |-------------------------------------------------------------------
     * |                          Validators
     * |-------------------------------------------------------------------
     * 
     * Custom validators
     */
    'validators' => [
        App\Http\Validators\LuckyNumbersValidator::class
    ]
];
```
We can then call our validator anywhere in the code by specifying its argument:

```php
<?php
 
namespace App\Http\Controllers;
 
use Clicalmani\Fundation\Http\Requests\Request;
use Clicalmani\Fundation\Validation\AsValidator;
 
class UserController extends Controller
{
    /**
     * Update the given user.
     */
    #[AsValidator(
        choice: 'required|lucky[]|join:1'
    )]
    public function show(Request $request)
    {
        $choice = $request->choice; // 23, 45, 67, 89, 47
    }
}
```
!> Sometime you may need to override a built-in validator. In our case we have overriden `Clicalmani\Fundation\Validation\Validators\ObjectsValidator` class to create `LyckyNumbersValidator`.

