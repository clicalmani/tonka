**<h2>Requests</h2>**

## Introduction
Tonka's `Clicalmani\Fundation\Http\Requests\Request` class provides an object-oriented way to interact with the current HTTP request being handled by your application as well as retrieve the input, cookies, and files that were submitted with the request.

## Accessing the Request
To obtain an instance of the current HTTP request via dependency injection, you should type-hint the `Clicalmani\Fundation\Http\Requests\Request` class on your route controller method. The incoming request instance will automatically be injected by the service container:

```php
<?php
 
namespace App\Http\Controllers;

use Clicalmani\Fundation\Http\Requests\Request;
 
class UserController extends Controller
{
    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $name = $request->name;
 
        // Store the user...
 
        return response()->redirect()->route('/users');
    }
}
```
## Dependency Injection and Route Parameters
If your controller method is also expecting input from a route parameter you should list your route parameters after your other dependencies. For example, if your route is defined like so:

```php
use App\Http\Controllers\UserController;
 
Route::put('/user/:id', [UserController::class, 'update']);
```
You may still type-hint the `Clicalmani\Fundation\Http\Requests\Request` and access your id route parameter by defining your controller method as follows:

```php
<?php
 
namespace App\Http\Controllers;

use Clicalmani\Fundation\Http\Requests\Request;
 
class UserController extends Controller
{
    /**
     * Update the specified user.
     */
    public function update(Request $request, int $id)
    {
        // Update the user...
 
        return response()->success();
    }
}
```