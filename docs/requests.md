## Introduction

Requests are a fundamental part of web development and API interactions. They allow clients to communicate with servers, retrieve data, and perform various operations. Understanding how to make and handle requests is crucial for building robust and efficient applications. This documentation will guide you through the basics of making requests, handling responses, and troubleshooting common issues.

## Interacting With The Request

### Accessing the Request

To interact with a request, you need to understand its components and how to access them. Here are some key aspects:

- **URL**: The endpoint to which the request is sent.
- **Method**: The HTTP method (GET, POST, PUT, DELETE, etc.) used for the request.
- **Headers**: Metadata sent with the request, such as content type and authorization tokens.
- **Body**: The data sent with the request, typically used with POST and PUT methods.

#### Example in JavaScript using `fetch`

```javascript
const url = 'https://api.example.com/data';
const options = {
    method: 'GET',
    headers: {
        'Authorization': 'Bearer YOUR_ACCESS_TOKEN'
    }
};

fetch(url, options)
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
```

In this JavaScript example the request will be sent to `api/data` route defined in your routes file:

```php
use App\Http\Controllers\DataController;

Route::get('/data', DataController::class);
```

As your `DataController` is an invokable controller, the request will be send to the `__invoke` method:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\RequestController as Controller;
use Clicalmani\Foundation\Http\Requests\Request;

class DataController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function __invoke(Request $request) : View
    {
        // Logic of your code

        return new View('');
    }
}
```

In this exemple we type-hint the `Clicalmani\Foundation\Http\Requests\Request` class on the `__invoke` method to make it possible to receive the request object. Tonka [Service Container]() will automatically inject the request object to the metod.

You may also type-hint the `Clicalmani\Foundation\Http\Requests\Request` class on a route closure. The service container will automatically inject the incoming request into the closure when it is executed:

```php
use Clicalmani\Foundation\Http\Requests\Request;
 
Route::get('/', function (Request $request) {
    // ...
});
```

### Dependency Injection and Route Parameters

If your controller method is also expecting input from a route parameter you should list your route parameters after your other dependencies. For example, if your route is defined like so:

```php
Route::get('/users/:id', [UserController::class, 'show']);
```

You can define your controller method to accept the route parameter:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class UserController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @param  int  $id
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function show(Request $request, int $id) : View
    {
        // Logic to retrieve user by $id

        return new View('user.profile', ['user' => $user]);
    }
}
```

In this example, the `show` method will receive both the request object and the `id` route parameter. The service container will automatically inject the request object and the route parameter into the method.

### Request URI, Host, and Method

To access the request URI, host, and method, you can use the following methods provided by the `Request` class:

- **URI**: To get the full URI of the request, use the `fullUrl` method.
- **Host**: To get the host of the request, use the `getHost` method.
- **Method**: To get the HTTP method of the request, use the `getMethod` method.

Here is an example of how to use these methods in a controller:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class ExampleController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function handle(Request $request) : View
    {
        $uri = $request->fullUrl();
        $host = $request->getHost();
        $method = $request->getMethod();

        // Logic to handle the request

        return new View('example.view', compact('uri', 'host', 'method'));
    }
}
```

In this example, the `handle` method retrieves the full URI, host, and HTTP method of the incoming request and passes them to the view.

### Verifying Request Path

The `route` method called on the request object return the current route the user visited. You may use the `named` method or the `is` method to verify if the incomming request has matched a named route or a given pattern:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class PathController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function checkPath(Request $request) : View
    {
        if ($request->route()->is('admin/*')) {
            // Logic for admin routes
        }

        if ($request->route()->named('named.*')) {
            // Logic for named routes
        }

        // Other logic

        return new View('path.view');
    }
}
```

In this example, the `checkPath` method checks if the request path starts with `admin/`. If it does, it executes the logic for admin routes. It also checks if the request matches a [named route](routing.md?id=named-routes) using the `routeIs` method. If it does, it executes the logic for named routes.

### Retrieving the Request URL

To retrieve the URL of the incoming request, you can use the `url` method provided by the `Request` class. This method returns the URL without the query string. If you need the full URL including the query string, you can use the `fullUrl` method.

Here is an example of how to use these methods in a controller:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class UrlController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showUrl(Request $request) : View
    {
        $url = $request->url();
        $uri = $request->route()->uri(); // The URI of the route as defined in the route file.
        $fullUrl = $request->fullUrl();

        // Logic to handle the request

        return new View('url.view', compact('url', 'uri', 'fullUrl'));
    }
}
```

In this example, the `showUrl` method retrieves both the URL and the full URL of the incoming request and passes them to the view.

If you would like to append query string data to the current URL, you may call the fullUrlWithQuery method. This method merges the given array of query string variables with the current query string:

```php
$request->fullUrlWithQuery(['type' => 'phone']);
```

If you would like to get the current URL without a given query string parameter, you may utilize the fullUrlWithoutQuery method:

```php
$request->fullUrlWithoutQuery(['type']);
```

### Retrieving the Request Host

You may retrieve the "host" of the incoming request via the `host`, `httpHost`, and `schemeAndHttpHost` methods:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class HostController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showHost(Request $request) : View
    {
        $host = $request->getHost();
        $httpHost = $request->getHttpHost();
        $schemeAndHttpHost = $request->getSchemeAndHttpHost();

        // Logic to handle the request

        return new View('host.view', compact('host', 'httpHost', 'schemeAndHttpHost'));
    }
}
```

In this example, the `showHost` method retrieves the host, HTTP host, and scheme and HTTP host of the incoming request and passes them to the view.

### Retrieving the Request Method

To retrieve the HTTP method of the incoming request, you can use the `getMethod` method provided by the `Request` class. This method returns the HTTP method used for the request, such as GET, POST, PUT, DELETE, etc. You may use the isMethod method to verify that the HTTP verb matches a given string:

Here is an example of how to use this method in a controller:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class MethodController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showMethod(Request $request) : View
    {
        $method = $request->getMethod();
        $is_method = $request->isMethod('get|post');

        // Logic to handle the request

        return new View('method.view', compact('method', 'is_method'));
    }
}
```

In this example, the `showMethod` method retrieves the HTTP method of the incoming request and passes it to the view.

### Retrieving Request Headers

You may retrieve a request header from the `Clicalmani\Foundation\Http\Requests\Request` instance using the `header` method. If the header is not present on the request, `null` will be returned. However, the `header` method accepts an optional second argument that will be returned if the header is not present on the request:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class HeaderController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showHeader(Request $request) : View
    {
        $header = $request->header('X-Custom-Header', 'default_value');

        // Logic to handle the request

        return new View('header.view', compact('header'));
    }
}
```

In this example, the `showHeader` method retrieves the value of the `X-Custom-Header` header from the incoming request. If the header is not present, it returns `'default_value'`.

For convenience, the `bearerToken` method may be used to retrieve a bearer token from the Authorization header. If no such header is present, an empty string will be returned:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class TokenController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showToken(Request $request) : View
    {
        $token = $request->bearerToken();

        // Logic to handle the request

        return new View('token.view', compact('token'));
    }
}
```

In this example, the `showToken` method retrieves the bearer token from the Authorization header of the incoming request and passes it to the view.

### Retrieving the Client IP Address

The `ip` method may be used to retrieve the IP address of the client that made the request to your application. This can be useful for logging, analytics, or security purposes.

Here is an example of how to use the `ip` method in a controller:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class IpController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showIp(Request $request) : View
    {
        $ipAddress = $request->ip();

        // Logic to handle the request

        return new View('ip.view', compact('ipAddress'));
    }
}
```

In this example, the `showIp` method retrieves the IP address of the client that made the request and passes it to the view.

### Inspecting Requested Content Types

Tonka provides several methods for inspecting the incoming request's requested content types via the `Accept` header. First, the `getAcceptableContentTypes` method will return an array containing all of the content types accepted by the request:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Requests\Request;
use Clicalmani\Foundation\Resources\View;

class ContentTypeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\Requests\Request  $request
     * @return \Clicalmani\Foundation\Resources\View
     */
    public function showContentTypes(Request $request) : View
    {
        $acceptableContentTypes = $request->getAcceptableContentTypes();

        // Logic to handle the request

        return new View('content_types.view', compact('acceptableContentTypes'));
    }
}
```

In this example, the `showContentTypes` method retrieves the acceptable content types from the `Accept` header of the incoming request and passes them to the view.

If you would like to determine if the request accepts a given content type, you may use the `accepts` method:

```php
if ($request->accepts('application/json')) {
    // Logic for handling JSON requests
}
```

To determine if the request prefers a given content type out of a list of content types, you may use the `prefers` method:

```php
$contentType = $request->prefers(['text/html', 'application/json']);
```

The `prefers` method will return the content type that is most preferred by the request based on the `Accept` header.

Since many applications only serve HTML or JSON, you may use the `expectsJson` method to quickly determine if the incoming request expects a JSON response:

```php
if ($request->expectsJson()) {
    // Logic for handling requests that expect a JSON response
}
```

## Input

### Retrieving Input

To retrieve input from the request, you can use various methods provided by the `Request` class. These methods allow you to access query parameters, form data, and JSON payloads.

#### Retrieving All Input Data

To retrieve all input data from the request, use the `all` method:

```php
$input = $request->all();
```

#### Retrieving Input as a Collection

Using the `collect` method, you may retrieve all of the incoming request's input data as a collection:

```php
$input = $request->collect();
```

The `collect` method also allows you to retrieve a subset of the incoming request's input as a collection:

```php
$subset = $request->collect('users');
```

In this example, the `collect` method retrieves only the `users` input values from the request and returns them as a collection.

#### Retrieving a Specific Input Value

To retrieve a specific input value, use the `input` method:

```php
$name = $request->input('name');
```

You can also provide a default value if the input is not present:

```php
$name = $request->input('name', 'default_name');
```

#### Retrieving Query Parameters

To retrieve query parameters from the URL, use the `query` method:

```php
$page = $request->query('page');
```

#### Retrieving JSON Input

If the incoming request contains JSON data, you can use the `json` method to retrieve it:

```php
$data = $request->json()->all();
```

#### Retrieving Input from a Nested Array

To retrieve input from a nested array, use "dot" notation:

```php
$city = $request->input('address.city');
```

#### Checking for Input Presence

To check if a specific input value is present, use the `has` method:

```php
if ($request->has('name')) {
    // Logic if 'name' is present
}
```

When given an array, the has method will determine if all of the specified values are present:

```php
if ($request->has(['name', 'email'])) {
    // Logic if both 'name' and 'email' are present
}
```

#### Retrieving Old Input

To retrieve old input data (e.g., after form validation failure), use the `old` method:

```php
$oldName = $request->old('name');
```

These methods provide a flexible way to access and manipulate input data from the request, making it easier to handle user input in your application.