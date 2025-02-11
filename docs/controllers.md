- [Introduction](controllers.md?id=introduction)
- [Writing Controllers](controllers.md?id=writing-controllers)
    - [Basic Controller](controllers.md?id=basic-controller)
    - [Single Action Controllers](controllers.md?id=single-action-controllers)
    - [Controller Middleware](controllers.md?id=controller-middleware)
        - [Assigning Middleware in Routes](controllers.md?id=assigning-middleware-in-routes)
        - [Assigning Middleware in Controller Constructor](controllers.md?id=assigning-middleware-in-controller-constructor)
    - [Resource Controllers](controllers.md?id=resource-controllers)
        - [Customizing Missing Model Behavior](controllers.md?id=customizing-missing-model-behavior)
        - [Soft Deleted Models](controllers.md?id=soft-deleted-models)
        - [Enabling Soft Deletes](controllers.md?id=enabling-soft-deletes)
        - [Querying Soft Deleted Models](controllers.md?id=querying-soft-deleted-models)
        - [Restoring Soft Deleted Models](controllers.md?id=restoring-soft-deleted-models)
        - [Permanently Deleting Models](controllers.md?id=permanently-deleting-models)
        - [Specifying the Resource Model](controllers.md?id=specifying-the-resource-model)
        - [Generating Form Requests](controllers.md?id=generating-form-requests)
        - [Partial Resource Routes](controllers.md?id=partial-resource-routes)
            - [Using `only`](controllers.md?id=using-only)
            - [Using `except`](controllers.md?id=using-except)
        - [API Resource Routes](controllers.md?id=api-resource-routes)
        - [Nested Resources](controllers.md?id=nested-resources)
            - [Scoping Nested Resources](controllers.md?id=scoping-nested-resources)
            - [Shallow Nesting](controllers.md?id=shallow-nesting)
        - [Naming Resource Routes](controllers.md?id=naming-resource-routes)
        - [Localizing Resource URIs](controllers.md?id=localizing-resource-uris)
        - [Supplementing Resource Controllers](controllers.md?id=supplementing-resource-controllers)
        - [Singleton Resource Controllers](controllers.md?id=singleton-resource-controllers)
            - [Nested Singleton Resources](controllers.md?id=nested-singleton-resources)
            - [Creatable Singleton Resources](controllers.md?id=creatable-singleton-resources)
            - [API Singleton Resources](controllers.md?id=api-singleton-resources)
- [Dependency Injection and Controllers](controllers.md?id=dependency-injection-and-ontrollers)
    - [Constructor Injection](controllers.md?id=constructor-injection)
    - [Method Injection](controllers.md?id=method-injection)
- [Adding Validation Attribute](controllers.md?id=adding-validation-attribute)
- [Testing Controllers](controllers.md?id=testing-controllers)
    - [Creating Tests for Controllers](controllers.md?id=creating-tests-for-controllers)
    - [Writing Tests](controllers.md?id=writing-tests)
    - [Running Tests](controllers.md?id=running-tests)
    - [Using Sequences](controllers.md?id=using-sequences)

## Introduction

Instead of defining all of your request handling logic as closures in your route files, you may wish to organize this behavior using "**controller**" classes. Controllers can group related request handling logic into a single class. For example, a `UserController` class might handle all incoming requests related to users, including `showing`, `creating`, `updating`, and `deleting` users.

## Writing Controllers

### Basic Controller

To quickly generate a new controller, you may run the `make:controller` console command. By default, all of the controllers for your application are stored in the `app/Http/Controllers` directory:

```bash
php tonka make:controller UserController
```

This command will create a new controller file at `app/Http/Controllers/UserController.php`. You can then define methods within this controller to handle various actions related to users.

Here is an example of a basic controller class:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class UserController extends Controller
{
    // Show a list of users
    public function index()
    {
        // Logic to retrieve and return a list of users
    }

    // Show a single user
    public function show(int $id)
    {
        // Logic to retrieve and return a single user by their ID
    }

    // Show the form to create a new user
    public function create()
    {
        // Logic to show a form for creating a new user
    }

    // Store a new user
    public function store(Request $request)
    {
        // Logic to store a new user in the database
    }

    // Show the form to edit an existing user
    public function edit(int $id)
    {
        // Logic to show a form for editing an existing user
    }

    // Update an existing user
    public function update(Request $request, int $id)
    {
        // Logic to update an existing user in the database
    }

    // Delete a user
    public function destroy(int $id)
    {
        // Logic to delete a user from the database
    }
}
```

This controller includes methods for handling common user-related actions such as listing users, showing a single user, creating a new user, editing an existing user, updating a user, and deleting a user.

Once you have written a controller class and method, you may define routes to the controller method like so:

```php
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/:id', [UserController::class, 'show']);
Route::get('/users/create', [UserController::class, 'create']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/:id/edit', [UserController::class, 'edit']);
Route::put('/users/:id', [UserController::class, 'update']);
Route::delete('/users/:id', [UserController::class, 'destroy']);
```

These routes will map HTTP requests to the corresponding methods in the `UserController` class, allowing you to handle user-related actions through your controller.

!> Do not forget to type-hint your controller methods parameters otherwise null will be used as default type.

### Single Action Controllers

If you have a controller that only handles a single action, you may place that single action in an invokable controller. To define an invokable controller, simply place a single `__invoke` method on the controller:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class ShowProfileController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @param  int  $id
     * @return \Clicalmani\Foundation\Http\Response
     */
    public function __invoke(Request $request, int $id)
    {
        // Logic to show a user's profile by their ID
    }
}
```

When registering routes for single action controllers, you do not need to specify a method:

```php
use App\Http\Controllers\ShowProfileController;

Route::get('/user/:id', ShowProfileController::class);
```

This route will map the incoming request to the `__invoke` method on the `ShowProfileController` class.

You may generate an invokable controller by using the `--invokable` option of the `make:controller` console command:

```sh
php tonka make:controller ShowProfileController --invokable
```

### Controller Middleware

Middleware can be assigned to the controller's routes in your route files or within your controller's constructor. Middleware provide a convenient mechanism for filtering HTTP requests entering your application.

#### Assigning Middleware in Routes

You can assign middleware to a controller route directly in your route files using the `middleware` method:

```php
use App\Http\Controllers\UserController;
use App\Http\Middlewares\PreventRouteTampering;

Route::get('/users', [UserController::class, 'index'])->middleware('auth');
Route::get('/users/:id', [UserController::class, 'show'])->middleware('auth');
Route::get('/users/:id/?:hash', [UserController::class, 'show'])->middleware(PreventRouteTempering::class);
```

#### Assigning Middleware in Controller Constructor

Alternatively, you can assign middleware within your controller's constructor. This approach allows you to assign middleware to all methods in the controller or to specific methods:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Traits\HasMiddleware;

class UserController extends Controller
{
    use HasMiddleware;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('log')->only('index');
        $this->middleware('subscribed')->except(['store']);
    }

    // Controller methods...
}
```

In this example, the `auth` middleware will be applied to all methods, the `log` middleware will only be applied to the `index` method, and the `subscribed` middleware will be applied to all methods except the `store` method.

Using middleware in your controllers helps to keep your route files clean and allows you to apply common filters to multiple routes in a centralized manner.

### Resource Controllers

Tonka provides an easy way to define resourceful routes to a controller. Resource controllers allow you to quickly create a controller that handles all CRUD operations for a given model.

To create a resource controller, you can use the `make:controller` console command with the `--resource` option:

```bash
php tonka make:controller UserController --resource
```

This command will generate a controller file at `app/Http/Controllers/UserController.php` containing methods for handling the typical CRUD operations: `index`, `create`, `store`, `show`, `edit`, `update`, and `destroy`.

Here is an example of a resource controller:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;
user App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function index() : View
    {
        // Implement the resource listing code

        return new View('');
    }

    /**
     * Create the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function create(Request $request) : View
    {
        // Implement the resource creation code

        return new View('');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function store(Request $request)
    {
        // Implement the resource storing code
    }

    /**
     * Show the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @param int $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function show(Request $request, int $id) : View
    {
        // Implement the resource view code

        return new View('');
    }

    /**
     * Edit the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @param int $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function edit(Request $request, int $id) : View
    {
        // Implement the resource edit code

        return new View('');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @param  int $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function update(Request $request, int $id)
    {
        // Your code
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @param int $id 
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function destroy(Request $request, int $id)
    {
        // Your code
    }
}
```

To define resource routes to the controller, you can use the `Route::resource` method:

```php
use App\Http\Controllers\UserController;

Route::resource('users', UserController::class);
```

This single route declaration creates multiple routes to handle a variety of actions on the `UserController`:

- `GET /users` - index
- `GET /users/create` - create
- `POST /users` - store
- `GET /users/:user` - show
- `GET /users/:user/edit` - edit
- `PUT/PATCH /users/:user` - update
- `DELETE /users/:user` - destroy

Using resource controllers and routes can help you quickly set up CRUD operations for your models in a standardized way.

#### Customizing Missing Model Behavior

When handling routes that involve models, you may want to customize the behavior when a model is not found. By default, Tonka will return a 404 HTTP response if an implicit model binding fails. However, you can customize this behavior by calling the `missing()` method on the route or by defining a `resolveRouteBinding` method on your model.

For exemple, let's customize the behavior for the `User` model by calling the `missing` method:

```php
use Clicalmani\Foundation\Exceptions\ModelNotFoundException;

Route::resource('users', UserController::class)
    ->missing(function() {
        throw new ModelNotFoundException('User not found', 404, 'NOT_FOUND');
    })
```

We can also do the same by defining the `resolveRouteBinding` method:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;
use Clicalmani\Foundation\Exceptions\ModelNotFoundException;

class User extends Model
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Clicalmani\Database\Factory\Models\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Custom logic to retrieve the model or return null
        return self::where($field ?? $this->getKey(), $value)->firstOr(function () {
            // Custom behavior when model is not found
            throw new ModelNotFoundException('User not found', 404, 'NOT_FOUND');
        });
    }
}
```

In this example, if the `User` model is not found, a 404 response with a custom message "User not found" will be returned.

You can also customize the behavior for all models by defining a global resolver in a service provider:

```php
<?php

namespace App\Providers;

use Clicalmani\Foundation\Providers\ServiceProvider;
use Clicalmani\Foundation\Exceptions\ModelNotFoundException;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        \Clicalmani\Database\Factory\Models\Model::resolveRouteBindingUsing(function ($value, $field = null, $model) {
            return $model->where($field ?? $model->getRouteKeyName(), $value)->firstOr(function () {
                // Custom behavior when model is not found
                throw new ModelNotFoundException('Model not found', 404, 'NOT_FOUND');
            });
        });
    }
}
```

This approach allows you to define a global behavior for all models when they are not found during route model binding.

By customizing the missing model behavior, you can provide more informative responses and handle missing models in a way that suits your application's needs.

#### Soft Deleted Models

When working with models that are soft deleted, you may want to customize the behavior to include soft deleted models in your queries or handle them differently. Tonka provides a convenient way to work with soft deleted models using the `SoftDelete` trait.

##### Enabling Soft Deletes

To enable soft deletes for a model, use the `SoftDelete` trait in your model and add a `deleted_at` column to your table:

```php
<?php

namespace App\Models;

use Clicalmani\Foundation\Database\Eloquent\Model;
use Clicalmani\Database\Traits\SoftDelete;

class User extends Model
{
    use SoftDelete;

    /**
     * Date attributes
     * 
     * @var string[]
     */
    protected $dates = ['deleted_at'];
}
```

Next, add the `deleted_at` column to your table by implementing the `alter` method in `UserEntity`:

```php
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\AlterOption;

#[AlterOption] // Notice here AlterOption attribute!
class UserEntity extends Entity
{
    public function alter(AlterOption $table) : string
    {
        $table->addColumn('deleted_at')->dateTime()->nullable()->after('email');
        return $table->render();
    }
}
```

After defining the `UserEntity` class with the `alter` method, you need to run the migration to apply the changes to your database. You can do this by running the `migrate:entity` console command:

```bash
php tonka migrate:entity User
```

This command will execute the migration and add the `deleted_at` column to the `users` table, enabling soft deletes for the `User` model.

##### Querying Soft Deleted Models

When querying a model that uses soft deletes, the `deleted_at` column will be automatically checked to exclude soft deleted models. To include soft deleted models in your query, use the `withTrashed` method:

```php
$users = User::withTrashed()->get();
```

To only retrieve soft deleted models, use the `onlyTrashed` method:

```php
$trashedUsers = User::onlyTrashed()->get();
```

##### Restoring Soft Deleted Models

To restore a soft deleted model, use the `restore` method:

```php
$user = User::withTrashed()->fetch()
            ->each(fn(User $user) => $user->restore());
```

##### Permanently Deleting Models

To permanently delete a soft deleted model, use the `forceDelete` method:

```php
$user = User::withTrashed()->find($id);
$user->forceDelete();
```

By using the `SoftDelete` trait, you can easily manage soft deleted models and customize their behavior in your application.

##### Specifying the Resource Model

If you are using route model binding and would like the resource controller's methods to type-hint a model instance, you may use the `--model` option when generating the controller:

```bash
php tonka make:controller UserController --resource --model=User
```

This command will generate a resource controller with methods that type-hint the `User` model instance, making it easier to work with the model in your controller actions.

When defining resource controllers, you can specify the model that the controller should interact with by using route model binding. This allows you to automatically inject the model instance into your controller methods based on the route parameters.

For example, let's update the `UserController` to use route model binding for the `User` model:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;
use \Clicalmani\Foundation\Resources\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function index()
    {
        // Implement the resource listing here
    }

    /**
     * Create the specified resource in storage.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @param \App\Models\User $user
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function create(Request $request, User $user) : View
    {
        // Implement the resource create here

        return new View('');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @param \App\Models\User $user
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function store(Request $request, User $user)
    {
        // Implement the resource storage here
    }

    /**
     * Show the specified resource.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @param \App\Models\User $user
     * @param int $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function show(Request $request, User $user, int $id) : View
    {
        // Implement the resource view here

        return new View('');
    }

    /**
     * Edit the specified resource.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @param \App\Models\User $user
     * @param int $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function edit(Request $request, User $user, int $id) : View
    {
        // Implement the resource edit here

        return new View('');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @param \App\Models\User $user
     * @param  int $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function update(Request $request, User $user, int $id)
    {
        // Implement the resource update here
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @param \App\Models\User $user
     * @param int $id 
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function destroy(Request $request, User $user, int $id)
    {
        // Implement the resource destroy here
    }
}
```

In this example, the `show`, `edit`, `store`, `update`, and `destroy` methods will automatically receive the `User` model instance based on the route parameter.

To define resource routes with model binding, you can use the `Route::resource` method as usual:

```php
use App\Http\Controllers\UserController;

Route::resource('users', UserController::class);
```

With this setup, the `UserController` methods will automatically receive the `User` model instance, making it easier to work with the model in your controller actions.

##### Generating Form Requests

You may provide the `--requests` option when generating a resource controller to instruct Console to generate form request classes for the controller's storage and update methods:

```bash
php tonka make:controller UserController --resource --model=User --requests
```

This command will generate a resource controller along with form request classes for the `store` and `update` methods. The form request classes will be created in the `app/Http/Requests` directory.

Here is an example of the generated form request classes:

```php
<?php

namespace App\Http\Requests;

use Clicalmani\Foundation\Http\Request;

class StoreUserRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function signatures()
    {
        return $this->merge([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8|max:255|confirm:1|hash:1',
            'password_confirm' => 'sometimes|required|string|min:8|max:255'
        ]);
    }
}
```

```php
<?php

namespace App\Http\Requests;

use Clicalmani\Foundation\Http\Request;

class UpdateUserRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function signatures()
    {
        return $this->merge([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|password|min:8|max:255|confirm:1|hash:1',
            'password_confirm' => 'sometimes|required|string|min:8|max:255'
        ]);
    }
}
```

In your `UserController`, you can now use these form request classes for the `store` and `update` methods:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Clicalmani\Foundation\Http\RequestController as Controller;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        // Logic to store a new user in the database
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Logic to update an existing user in the database
    }
}
```

Using the `--requests` option helps to streamline the creation of form request classes and ensures that your controller methods are clean and maintainable.

##### Partial Resource Routes

If you don't need to define all of the resource routes, you can use the `only` or `except` methods to specify which routes should be included or excluded:

###### Using `only`

```php
use App\Http\Controllers\UserController;

Route::resource('users', UserController::class)->only([
    'index', 'show'
]);
```

This will create routes for only the `index` and `show` methods.

###### Using `except`

```php
use App\Http\Controllers\UserController;

Route::resource('users', UserController::class)->except([
    'create', 'edit'
]);
```

This will create routes for all methods except `create` and `edit`.

Using partial resource routes allows you to have more control over which routes are generated for your resource controllers, ensuring that only the necessary routes are included in your application.

##### API Resource Routes

When building APIs, you may want to define resource routes that return JSON responses instead of views. Tonka provides a convenient way to define API resource routes using the `apiResource` method.

To create an API resource controller, you can use the `make:controller` console command with the `--api` option:

```bash
php tonka make:controller UserController --api
```

This command will generate a controller file at `app/Http/Controllers/UserController.php` containing methods for handling the typical CRUD operations: `index`, `store`, `show`, `update`, and `destroy`.

Here is an example of an API resource controller class:

```php
<?php 
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function index()
    {
        // Implement the resource listing here
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function store(Request $request)
    {
        // Implement the resource storage here
    }

    /**
     * Show the specified resource.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function show(Request $request)
    {
        // Implement the resource view here
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function update(Request $request)
    {
        // Implement the resource update here
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Clicalmani\Foundation\Http\Request  $request
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function destroy(Request $request)
    {
        // Implement the resource destroy here
    }
}
```

To define API resource routes to the controller, you can use the `Route::apiResource` method:

```php
use App\Http\Controllers\UserController;

Route::apiResource('users', UserController::class);
```

This single route declaration creates multiple routes to handle a variety of actions on the `UserController`:

- `GET /users` - index
- `POST /users` - store
- `GET /users/:user` - show
- `PUT/PATCH /users/:user` - update
- `DELETE /users/:user` - destroy

Using API resource controllers and routes can help you quickly set up CRUD operations for your models in a standardized way, returning JSON responses suitable for APIs.

##### Nested Resources

Sometimes you may need to define routes to a nested resource. For example, a photo resource may be nested within a user resource. To define such routes, you can use the `Route::resource` method within a group of routes that define the parent resource:

```php
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhotoController;

Route::resource('users.photos', PhotoController::class);
```

This will create nested routes for the `PhotoController` within the context of a user:

- `GET /users/:user/photos` - index
- `GET /users/:user/photos/create` - create
- `POST /users/:user/photos` - store
- `GET /users/:user/photos/:photo` - show
- `GET /users/:user/photos/:photo/edit` - edit
- `PUT/PATCH /users/:user/photos/:photo` - update
- `DELETE /users/:user/photos/:photo` - destroy

In your `PhotoController`, you can access the parent user model through route parameters:

```php
<?php 
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Http\Request;
use \Clicalmani\Foundation\Resources\View;
use App\Models\User;
use App\Models\Photo;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function index()
    {
        // Implement the resource listing here
    }

    /**
     * Create the specified resource in storage.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function create(Request $request) : View
    {
        // Implement the resource create here

        return new View('');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Clicalmani\Foundation\Http\Request $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function store(Request $request)
    {
        // Implement the resource storage here
    }

    /**
     * Show the specified resource.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @param int $user_id
     * @param int $photo_id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function show(User $user, Photo $photo, int $user_id, int $photo_id) : View
    {
        // Implement the resource view here

        return new View('');
    }

    /**
     * Edit the specified resource.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @param int $user_id
     * @param int $photo_id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function edit(User $user, Photo $photo, int $user_id, int $photo_id) : View
    {
        // Implement the resource edit here

        return new View('');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @param  int $user_id
     * @param int $photo_id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function update(User $user, Photo $photo, int $user_id, int $photo_id)
    {
        // Implement the resource update here
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @param int $user_id 
     * @param int $photo_id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function destroy(User $user, Photo $photo, int $user_id, int $photo_id)
    {
        // Implement the resource destroy here
    }
}
```

Using nested resources helps to organize your routes and controllers when dealing with related models, providing a clear structure for handling nested data.

###### Scoping Nested Resources

When working with nested resources, you may want to scope the nested resource routes to ensure that they are only accessible within the context of their parent resource. This can be achieved by using the `scoped` method when defining the nested resource routes.

For example, let's define nested routes for photos within the context of a user, and scope the photo routes to the user's photos:

```php
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhotoController;

Route::resource('users.photos', PhotoController::class)->scoped([
    'photo' => 'slug',
]);
```

In this example, the `photo` route parameter will be scoped to the `slug` attribute of the `Photo` model. This means that the `Photo` model will be retrieved based on its `slug` attribute instead of its primary key.

Here is an example of the `PhotoController` with scoped routes:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Photo;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class PhotoController extends Controller
{
    public function index(User $user)
    {
        // Logic to retrieve and return a list of photos for the user
    }

    public function create(User $user)
    {
        // Logic to show a form for creating a new photo for the user
    }

    public function store(Request $request, User $user)
    {
        // Logic to store a new photo for the user in the database
    }

    public function show(User $user, Photo $photo)
    {
        // Logic to retrieve and return a single photo for the user
    }

    public function edit(User $user, Photo $photo)
    {
        // Logic to show a form for editing an existing photo for the user
    }

    public function update(Request $request, User $user, Photo $photo)
    {
        // Logic to update an existing photo for the user in the database
    }

    public function destroy(User $user, Photo $photo)
    {
        // Logic to delete a photo for the user from the database
    }
}
```

By scoping nested resources, you can ensure that the nested resource routes are properly constrained to their parent resource, providing a more intuitive and secure routing structure for your application.

###### Shallow Nesting

When working with deeply nested resources, it can be beneficial to use shallow nesting to avoid overly complex route definitions. Shallow nesting allows you to define routes for nested resources without including the parent resource's identifier in every route.

For example, let's define shallow nested routes for photos within the context of a user:

```php
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhotoController;

Route::resource('users.photos', PhotoController::class)->shallow();
```

This will create the following routes:

- `GET /users/:user/photos` - index
- `GET /users/:user/photos/create` - create
- `POST /users/:user/photos` - store
- `GET /photos/:photo` - show
- `GET /photos/:photo/edit` - edit
- `PUT/PATCH /photos/:photo` - update
- `DELETE /photos/:photo` - destroy

In your `PhotoController`, you can access the parent user model through route parameters for the `index`, `create`, and `store` methods, while the other methods will only require the photo identifier:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Photo;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class PhotoController extends Controller
{
    public function index(User $user)
    {
        // Logic to retrieve and return a list of photos for the user
    }

    public function create(User $user)
    {
        // Logic to show a form for creating a new photo for the user
    }

    public function store(Request $request, User $user)
    {
        // Logic to store a new photo for the user in the database
    }

    public function show(Photo $photo)
    {
        // Logic to retrieve and return a single photo
    }

    public function edit(Photo $photo)
    {
        // Logic to show a form for editing an existing photo
    }

    public function update(Request $request, Photo $photo)
    {
        // Logic to update an existing photo in the database
    }

    public function destroy(Photo $photo)
    {
        // Logic to delete a photo from the database
    }
}
```

Using shallow nesting helps to simplify your route definitions and makes your routes easier to manage, especially when dealing with deeply nested resources.

##### Naming Resource Routes

When defining resource routes, you may want to customize the names of the routes. Tonka allows you to specify custom names for your resource routes using the `names` method.

For example, let's define resource routes for the `UserController` with custom names:

```php
use App\Http\Controllers\UserController;

Route::resource('users', UserController::class)->names([
    'index' => 'users.list',
    'create' => 'users.create',
    'store' => 'users.store',
    'show' => 'users.show',
    'edit' => 'users.edit',
    'update' => 'users.update',
    'destroy' => 'users.delete',
]);
```

In this example, each route is assigned a custom name, which can be used when generating URLs or redirecting within your application.

You can also specify custom names for nested resource routes:

```php
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhotoController;

Route::resource('users.photos', PhotoController::class)->names([
    'index' => 'users.photos.list',
    'create' => 'users.photos.create',
    'store' => 'users.photos.store',
    'show' => 'users.photos.show',
    'edit' => 'users.photos.edit',
    'update' => 'users.photos.update',
    'destroy' => 'users.photos.delete',
]);
```

Using custom names for your resource routes helps to make your route definitions more readable and allows you to easily reference routes throughout your application.

##### Localizing Resource URIs

If your application supports multiple languages, you may want to localize the URIs of your resource routes. Tonka allows you to easily localize resource URIs by defining localized route groups.

For example, let's define localized resource routes for the `UserController`:

```php
use App\Http\Controllers\UserController;

Route::group(['prefix' => ':locale', 'where' => ['locale' => 'required|regex|pattern:[a-zA-Z]{2}']], function () {
    Route::resource('users', UserController::class);
});
```

In this example, the `locale` parameter will be included in the URI, allowing you to handle requests for different languages. The `where` clause ensures that the `locale` parameter matches a two-letter language code.

You can also define localized nested resource routes:

```php
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhotoController;

Route::group(['prefix' => ':locale', 'where' => ['locale' => 'required|regex|pattern:[a-zA-Z]{2}']], function () {
    Route::resource('users.photos', PhotoController::class);
});
```

This will create localized nested routes for the `PhotoController` within the context of a user.

To generate URLs for localized routes, you can use the `route` helper and pass the `locale` parameter:

```php
$url = route('users.index', app()->getLocale());
```

By localizing resource URIs, you can provide a better user experience for your application's international audience, ensuring that your routes are accessible in multiple languages.

##### Supplementing Resource Controllers

In addition to the standard methods provided by resource controllers, you may sometimes need to add custom methods to handle specific actions that are not covered by the default CRUD operations. You can easily add custom methods to your resource controllers and define routes for these methods.

For example, let's add a custom method to the `UserController` to handle user activation:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class UserController extends Controller
{
    // Existing resource methods...

    public function activate(User $user)
    {
        // Logic to activate the user
        $user->activate();
        return response()->json(['message' => 'User activated successfully']);
    }
}
```

To define a route for the custom method, you can use the `Route::post` method:

```php
use App\Http\Controllers\UserController;

Route::post('users/:user/activate', [UserController::class, 'activate'])->name('users.activate');
```

This route will map the `POST /users/:user/activate` request to the `activate` method in the `UserController`.

By supplementing resource controllers with custom methods, you can handle additional actions that are specific to your application's requirements, providing a flexible and extensible way to manage your resources.

##### Singleton Resource Controllers

In some cases, you may have a resource that should only have a single instance, such as a settings page. For such resources, you can define singleton resource controllers.

To create a singleton resource controller, you can use the `make:controller` console command with the `--singleton` option:

```bash
php tonka make:controller SettingsController --singleton
```

This command will generate a controller file at `app/Http/Controllers/SettingsController.php` containing methods for handling the typical CRUD operations: `show`, `edit`, `update`.

Here is an example of a singleton resource controller class:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class SettingsController extends Controller
{
    public function show()
    {
        // Logic to retrieve and return the settings
        return response()->json(Settings::first());
    }

    public function edit()
    {
        // Logic to show a form for editing the settings
    }

    public function update(Request $request)
    {
        // Logic to update the settings in the database
        $settings = Settings::first();
        $settings->update($request->all());
        return response()->json($settings);
    }
}
```

To define singleton resource routes to the controller, you can use the `Route::singleton` method:

```php
use App\Http\Controllers\SettingsController;

Route::singleton('settings', SettingsController::class);
```

This single route declaration creates routes to handle the actions on the `SettingsController`:

- `GET /settings` - show
- `GET /settings/edit` - edit
- `PUT/PATCH /settings` - update

Using singleton resource controllers and routes can help you manage resources that should only have a single instance in a standardized way.

###### Nested Singleton Resources

Singleton resources may also be nested within a standard resource. For example, you might have a settings resource that is unique to each user. To define nested singleton resource routes, you can use the `Route::singleton` method within a group of routes that define the parent resource:

```php
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSettingsController;

Route::resource('users', UserController::class);
Route::singleton('users.settings', UserSettingsController::class);
```

This will create the following routes for the `UserSettingsController` within the context of a user:

- `GET /users/:user/settings` - show
- `GET /users/:user/settings/edit` - edit
- `PUT/PATCH /users/:user/settings` - update

In your `UserSettingsController`, you can access the parent user model through route parameters:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSettings;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class UserSettingsController extends Controller
{
    public function show(User $user)
    {
        // Logic to retrieve and return the user's settings
        return response()->json($user->settings);
    }

    public function edit(User $user)
    {
        // Logic to show a form for editing the user's settings
    }

    public function update(Request $request, User $user)
    {
        // Logic to update the user's settings in the database
        $user->settings->update($request->all());
        return response()->json($user->settings);
    }
}
```

Using nested singleton resources helps to organize your routes and controllers when dealing with unique resources within the context of a parent resource, providing a clear structure for handling nested data.

###### Creatable Singleton Resources

In some cases, you may have a singleton resource that needs to be created before it can be shown, edited, or updated. To handle creatable singleton resources, you can define routes for creating and storing the singleton resource.

For example, let's create a `ProfileController` for managing a user's profile, which is a singleton resource that needs to be created:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class ProfileController extends Controller
{
    public function create()
    {
        // Logic to show a form for creating the profile
    }

    public function store(Request $request)
    {
        // Logic to store the profile in the database
        $profile = Profile::create($request->all());
        return response()->json($profile, 201);
    }

    public function show()
    {
        // Logic to retrieve and return the profile
        return response()->json(Profile::first());
    }

    public function edit()
    {
        // Logic to show a form for editing the profile
    }

    public function update(Request $request)
    {
        // Logic to update the profile in the database
        $profile = Profile::first();
        $profile->update($request->all());
        return response()->json($profile);
    }
}
```

To define routes for the creatable singleton resource, you can use the `Route::singleton` method along with additional routes for creating and storing the resource:

```php
use App\Http\Controllers\ProfileController;

Route::singleton('profile', ProfileController::class);
Route::get('profile/create', [ProfileController::class, 'create'])->name('profile.create');
Route::post('profile', [ProfileController::class, 'store'])->name('profile.store');
```

This will create the following routes for the `ProfileController`:

- `GET /profile/create` - create
- `POST /profile` - store
- `GET /profile` - show
- `GET /profile/edit` - edit
- `PUT/PATCH /profile` - update

Using creatable singleton resources allows you to handle the creation of singleton resources in a standardized way, ensuring that the resource is properly initialized before it can be managed.

###### API Singleton Resources

When building APIs, you may have singleton resources that should only have a single instance, such as a settings page. For such resources, you can define API singleton resource controllers.

To create an API singleton resource controller, you can use the `make:controller` console command with the `--api` and `--singleton` options:

```bash
php tonka make:controller SettingsController --singleton --api
```

This command will generate a controller file at `app/Http/Controllers/SettingsController.php` containing methods for handling the typical CRUD operations: `show`, `update`.

Here is an example of an API singleton resource controller class:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class SettingsController extends Controller
{
    public function show()
    {
        // Logic to retrieve and return the settings
        return response()->json(Settings::first());
    }

    public function update(Request $request)
    {
        // Logic to update the settings in the database
        $settings = Settings::first();
        $settings->update($request->all());
        return response()->json($settings);
    }
}
```

To define API singleton resource routes to the controller, you can use the `Route::singleton` method:

```php
use App\Http\Controllers\SettingsController;

Route::apiSingleton('settings', SettingsController::class);
```

This single route declaration creates routes to handle the actions on the `SettingsController`:

- `GET /settings` - show
- `PUT/PATCH /settings` - update

Using API singleton resource controllers and routes can help you manage resources that should only have a single instance in a standardized way, returning JSON responses suitable for APIs.

## Dependency Injection and Controllers

### Constructor Injection

Tonka allows you to take advantage of dependency injection in your controllers. You can type-hint dependencies in your controller's constructor, and they will be automatically resolved and injected by the service container.

For example, let's inject a repository into a controller:

```php
<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class UserController extends Controller
{
    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function index()
    {
        $users = $this->users->all();
        return response()->json($users);
    }

    // Other controller methods...
}
```

In this example, the `UserRepository` dependency is injected into the `UserController` constructor. The `index` method can then use the repository to retrieve a list of users.

Using constructor injection helps to decouple your controllers from specific implementations, making your code more modular and easier to test.

### Method Injection

In addition to constructor injection, Tonka also supports method injection. You can type-hint dependencies in your controller methods, and they will be automatically resolved and injected by the service container.

For example, let's inject a request instance into a controller method:

```php
<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\RequestController as Controller;

class UserController extends Controller
{
    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->users->create($data);
        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->users->update($id, $data);
        return response()->json($user);
    }

    // Other controller methods...
}
```

In this example, the `Request` instance is injected into the `store` and `update` methods. The methods can then use the request data to create or update a user.

If your controller method is also expecting input from a route parameter, list your route arguments after your other dependencies. For example, if your route is defined like so:

```php
use App\Http\Controllers\UserController;
 
Route::put('/user/:id', [UserController::class, 'update']);
```

You may still type-hint the `Clicalmani\Foundation\Http\Request` and access your `id` parameter by defining your controller method as follows:

```php
<?php
 
namespace App\Http\Controllers;
 
use Clicalmani\Foundation\Http\Request;
 
class UserController extends Controller
{
    /**
     * Update the given user.
     */
    public function update(Request $request, string $id)
    {
        // Update the user...
 
        return redirect('/users');
    }
}
```

Using method injection allows you to inject dependencies only where they are needed, making your controller methods more flexible and easier to test.

## Adding Validation Attribute

To add validation attribute to a controller, you can use the `Clicalmani\Validation\AsValidator` class provided by Tonka ORM. This class will check the request's attributes against the defined validation rules and throw a `ValidationException` if any of the rules are violated.

Here is an example of how to add validation to a controller method:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Http\Response;
use Clicalmani\Validation\Exceptions\ValidationException;
use Clicalmani\Validation\AsValidator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Clicalmani\Psr\Response
     */
    #[AsValidator(
        given_name: 'required|string|max:200',
        family_name: 'required|string|max:200',
        email: 'required|email|max:255'
    )]
    public function store()
    {
        $user = new User;
        $user->fill(request()->all());

        try {
            $user->save();

            return Response::success();
        } catch (ValidationException $e) {
            return Response::error($e->getMessage());
        }
    }
}
```

In this example, the `store` method in the `UserController` class creates a new `User` instance, fills it with data from the request, and validates the model's attributes. If the validation passes, the user is saved to the database. If the validation fails, a `ValidationException` is thrown, and an error response is returned.

!> For further information about validators, read the [Validation](orm.md?id=validating-model-instances) documentation.

### Testing Controllers

Tonka provides a simple and intuitive way to test your controllers. You can use the built-in testing tools to simulate HTTP requests and assert the responses. This allows you to ensure that your controllers are handling requests correctly and returning the expected responses.

#### Creating Tests for Controllers

To create tests for your controllers, you can use the `make:test` console command to generate a test class. For example, let's create a test class for the `UserController`:

```sh
php tonka make:test UserController
```

After running the command, a test class will be created in the `test` directory:

```php
<?php

namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;
use App\Http\Controllers\UserController;

class UserControllerTest extends ControllerTest
{
    /**
     * Controller class
     *
     * @var string Class name 
     */
    protected $controller = UserController::class;
    	
    /**
     * Seed index method
     * 
     * @return array 
     */
    public function index() : array
    {
        return [
            // Action parameters
        ];
    }

    /**
     * Seed store method
     * 
     * @return array 
     */
    public function store() : array
    {
        return [
            // Action parameters
        ];
    }

    /**
     * Seed create method
     * 
     * @return array 
     */
    public function create() : array
    {
        return [
            // Action parameters
        ];
    }

    /**
     * Seed update method
     * 
     * @return array 
     */
    public function update() : array
    {
        return [
            // Action parameters
        ];
    }

    /**
     * Seed destroy method
     * 
     * @return array 
     */
    public function destroy() : array
    {
        return [
            // Action parameters
        ];
    }

    /**
     * Test method
     * 
     * @return void 
     */
    public static function test() : void
    {
        // Test code
    }
}
```

#### Writing Tests

Now that we have a test class, we need to add `HasTest` trait to the `UserController` class to make sure that the controller and its test class are interconnected:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Traits\HasTest;

class UserController extends Controller
{
    use HasTest;

    // ...
}
```

Finally, we can write a test in the `test` method of UserControllerTest class:

```php
<?php

namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;
use App\Http\Controllers\UserController;

class UserControllerTest extends ControllerTest
{
    /**
     * Seed update method
     * 
     * @return array 
     */
    public function update() : array
    {
        return [
            'id' => 1,
            'email' => faker()->unique()->safeEmail
        ];
    }

    /**
     * Test method
     * 
     * @return void 
     */
    public static function test() : void
    {
        UserController::test('update')
            ->count(1)
            ->make();
    }
}
```

In the generated test class, we wrote tests to verify the `update` method's behavior. An HTTP request will be made to update the user's email address.

#### Running Tests

To run your tests, you can use the `test` console command provided by Tonka. This command will execute all the tests in your application and display the results.

```sh
php tonka test
```

You can also run a specific test class by providing the class name as an argument:

```sh
php tonka test --controller=UserController
```

#### Using Sequences

When testing controllers, you may need to perform multiple actions at a time. Tonka provides a convenient way to define and execute sequences of actions using the `Sequence` class.

For example, let's define a sequence of actions to test the `UserController`:

```php
<?php

namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;
use Clicalmani\Database\Factory\Sequence;
use App\Http\Controllers\UserController;

class UserControllerTest extends ControllerTest
{
    /**
     * Seed store method
     * 
     * @return array 
     */
    public function store() : array
    {
        return [
            'given_name' => null,
            'family_name' => null,
            'email' => null
        ];
    }

    /**
     * Test method
     * 
     * @return void 
     */
    public static function test() : void
    {
        UserController::test('store')
            ->count(3)
            ->defaultUsers()
            ->make();
    }

    public function defaultUsers()
    {
        return $this->state(function() {
            return [
                'given_name' => new Sequence(faker()->name(), faker()->name(), faker()->name()),
                'family_name' => new Sequence(faker()->name(), faker()->name(), faker()->name()),
                'email' => new Sequence(faker()->unique()->safeEmail, faker()->unique()->safeEmail, faker()->unique()->safeEmail)
            ];
        });
    }
}
```

In this example, we define 3 sequences of users to test the `store` methods of the `UserController`. The `Sequence` class allows us to store one user at a time.