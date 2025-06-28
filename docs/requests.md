- [Introduction](requests.md?id=introduction)
- [Interacting With The Request](requests.md?id=interacting-with-the-request)
    - [Accessing the Request](requests.md?id=accessing-the-request)
    - [Dependency Injection and Route Parameters](requests.md?id=dependency-injection-and-route-parameters)
    - [Request URI, Host, and Method](requests.md?id=request-uri,-host,-and-method)
        - [Retrieving the Request URL](requests.md?id=retrieving-the-request-url)
        - [Retrieving the Request Host](requests.md?id=retrieving-the-request-host)
        - [Retrieving the Request Method](requests.md?id=retrieving-the-request-method)
    - [Retrieving Request Headers](requests.md?id=retrieving-request-headers)
    - [Retrieving the Client IP Address](requests.md?id=retrieving-the-client-ip-address)
    - [Inspecting Requested Content Types](requests.md?id=inspecting-requested-content-types)
- [Input](requests.md?id=input)
    - [Retrieving Input](requests.md?id=retrieving-input)
    - [Retrieving All Input Data](requests.md?id=retrieving-all-input-data)
    - [Retrieving Input as a Collection](requests.md?id=retrieving-input-as-a-collection)
    - [Retrieving a Specific Input Value](requests.md?id=retrieving-a-specific-input-value)
    - [Retrieving Query Parameters](requests.md?id=retrieving-query-parameters)
    - [Retrieving JSON Input](requests.md?id=retrieving-json-input)
    - [Retrieving Input from a Nested Array](requests.md?id=retrieving-input-from-a-nested-array)
    - [Checking for Input Presence](requests.md?id=checking-for-input-presence)
- [Validating Requests](requests.md?id=validating-requests)
- [Files](requests.md?id=files)
    - [Retrieving Uploaded Files](requests.md?id=retrieving-uploaded-files)
    - [Retrieving a Single File](requests.md?id=retrieving-a-single-file)
    - [Checking if a File is Present](requests.md?id=checking-if-a-file-is-present)
    - [Validating Uploaded Files](requests.md?id=validating-uploaded-files)
    - [Storing Uploaded Files](requests.md?id=storing-uploaded-files)
    - [Retrieving the Original Filename](requests.md?id=retrieving-the-original-filename)
    - [Retrieving the File Extension](requests.md?id=retrieving-the-file-extension)
    - [Validating Successful Uploads](requests.md?id=validating-successful-uploads)
- [Configuring Trusted Proxies](requests.md?id=configuring-trusted-proxies)
    - [Setting Trusted Proxies](requests.md?id=setting-trusted-proxies)
    - [Configuring Trusted Headers](requests.md?id=configuring-trusted-headers)
    - [Configuring Trusted Hosts](requests.md?id=configuring-trusted-hosts)
    - [Setting Trusted Hosts](requests.md?id=setting-trusted-hosts)

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

use use Clicalmani\Foundation\Acme\Controller;
use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class DataController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function __invoke(Request $request) : ViewInterface
    {
        // Logic of your code

        return new View('');
    }
}
```

In this exemple we type-hint the `Clicalmani\Foundation\Http\Request` class on the `__invoke` method to make it possible to receive the request object. **Tonka** [Service Container](container.md) will automatically inject the request object to the metod.

You may also type-hint the `Clicalmani\Foundation\Http\Request` class on a route closure. The service container will automatically inject the incoming request into the closure when it is executed:

```php
use Clicalmani\Foundation\Http\RequestInterface as Request;
 
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class UserController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @param  int  $id
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function show(Request $request, int $id) : ViewInterface
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class ExampleController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function handle(Request $request) : ViewInterface
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class PathController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function checkPath(Request $request) : ViewInterface
    {
        if ($request->route()->is('admin/.*')) {
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

In this example, the `checkPath` method checks if the request path starts with `admin/`. If it does, it executes the logic for admin routes. It also checks if the request matches a [named route](routing.md?id=named-routes) using the `named` method. If it does, it executes the logic for named routes.

### Retrieving the Request URL

To retrieve the URL of the incoming request, you can use the `url` method provided by the `Request` class. This method returns the URL without the query string. If you need the full URL including the query string, you can use the `fullUrl` method.

Here is an example of how to use these methods in a controller:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class UrlController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showUrl(Request $request) : ViewInterface
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class HostController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showHost(Request $request) : ViewInterface
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class MethodController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showMethod(Request $request) : ViewInterface
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

You may retrieve a request header from the `Request` instance using the `header` method. If the header is not present on the request, `null` will be returned. However, the `header` method accepts an optional second argument that will be returned if the header is not present on the request:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class HeaderController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showHeader(Request $request) : ViewInterface
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class TokenController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showToken(Request $request) : ViewInterface
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

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class IpController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showIp(Request $request) : ViewInterface
    {
        $ipAddress = $request->ip();

        // Logic to handle the request

        return new View('ip.view', compact('ipAddress'));
    }
}
```

In this example, the `showIp` method retrieves the IP address of the client that made the request and passes it to the view.

### Inspecting Requested Content Types

**Tonka** provides several methods for inspecting the incoming request's requested content types via the `Accept` header. First, the `getAcceptableContentTypes` method will return an array containing all of the content types accepted by the request:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class ContentTypeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function showContentTypes(Request $request) : ViewInterface
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

Using the `collect` method, you may retrieve all of the incoming request's input data as a [collection](collection.md):

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

## Validating Requests

To ensure that incoming requests contain valid data, you can use the `validate` method provided by the `Request` class. This method allows you to define validation rules for the request data.

Here is an example of how to validate a request in a controller:

```php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Resources\View;

class ValidationController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Clicalmani\Foundation\Http\RequestInterface  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function validateRequest(Request $request) : ViewInterface
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'age' => 'nullable|integer|min:18',
        ]);

        // Logic to handle the validated data

        return new View('validation.view');
    }
}
```

In this example, the `validateRequest` method defines validation rules for the `name`, `email`, and `age` fields. If the validation fails, a `ValidationException` will be throw with an error message.

You can also customize the error messages by implementing the `message` method of your [custom validator](orm.md?id=creating-a-custom-validator):

```php
<?php

namespace App\Validators;

use Clicalmani\Validation\Validator;

class CustomValidator extends Validator
{
    /**
     * Validator argument
     * 
     * @var string
     */
    protected string $argument = 'custom';

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return sprintf("The %s field must be a valid custom name.", $this->parameter);
    }
}
```

This allows you to provide custom error messages for each validation rule.

For more complex validation logic, you can create a custom request class by extending the `Clicalmani\Foundation\Http\Request` class. This approach allows you to encapsulate the validation logic in a dedicated class.

Here is an example of a custom request class:

```php
namespace App\Http\Requests;

use Clicalmani\Foundation\Http\RequestInterface as Request;

class StoreUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return void
     */
    public function signatures() : void
    {
        $this->merge([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'age' => 'nullable|integer|min:18',
        ]);
    }
}
```

You can then use this custom request class in your controller:

```php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use Clicalmani\Foundation\Resources\View;

class UserController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Clicalmani\Foundation\Resources\ViewInterface
     */
    public function store(StoreUserRequest $request) : ViewInterface
    {
        return new View('user.store');
    }
}
```

In this example, the `store` method uses the `StoreUserRequest` class to validate the incoming request data. The validated data is then available for further processing.

You can create a custom request class by running the `make:request` command in the console. Here is an example:

```sh
php tonka make:request StoreUserRequest
```

After running this command the `StoreUserRequest.php` file will be available in the `app/Http/Requests` directory.

!> Once a custom request class is used in a controller method, the **Tonka** service container automatically invoke the `validate` method after automatically injecting the class.

## Files

### Retrieving Uploaded Files

To retrieve uploaded files from the request, you can use the `file` method provided by the `Request` class. This method allows you to access files that were uploaded via a form.

#### Retrieving a Single File

To retrieve a single file, use the `file` method with the name of the file input:

```php
$file = $request->file('photo');
```

#### Checking if a File is Present

To check if a file is present in the request, use the `hasFile` method:

```php
if ($request->hasFile('photo')) {
    // Logic if 'photo' file is present
}
```

#### Validating Uploaded Files

You can validate uploaded files using the `validate` method. For example, to ensure that an uploaded file is an image and does not exceed a certain size:

```php
$request->validate([
    'photo' => 'required|image|max:2048',
]);
```

#### Storing Uploaded Files

To store an uploaded file, use the `store` method. This method stores the file on the default disk and returns the path to the file:

```php
$file = $request->file('photo');
$file->store('photo.jpeg');
$file->moveTo(storage_path('/public/uploads/photo.jpeg'));
```

You can also specify a disk to store the file on:

```php
$path = $file->store('photo.jpg', 's3');
```

#### Retrieving the Original Filename

To retrieve the original filename of the uploaded file, use the `getClientFilename` method:

```php
$originalName = $file->getClientFilename();
```

#### Retrieving the File Extension

To retrieve the file extension of the uploaded file, use the `getClientExtension` method:

```php
$extension = $file->getClientExtension();
```

#### Retrieving the File Type

To retrieve the MIME type of the uploaded file, use the `getClientMediaType` method:

```php
$mimeType = $file->getClientMediaType();
```

This method returns the MIME type provided by the client, such as `image/jpeg` or `application/pdf`.

These methods provide a convenient way to handle file uploads in your application, ensuring that you can easily access, validate, and store uploaded files.

### Validating Successful Uploads

You may want to verify if the uploaded is valid. You can do this by calling the `isValid` method. 

Here is an example of how to validate a successful upload:

```php
if ($request->file('photo')?->isValid()) {
    // Logic if the file was successfully uploaded
} else {
    // Logic if the file upload failed
}
```

## Configuring Trusted Proxies

When your application is behind a proxy, you may need to configure trusted proxies to ensure that the request information is correctly interpreted. This is particularly important for applications that rely on the client's IP address or HTTPS detection.

### Setting Trusted Proxies

To configure trusted proxies, you can use the `setTrustedProxies` method provided by the `Request` class. This method allows you to specify which proxies are trusted and how to handle forwarded headers.

Here is an example of how to set trusted proxies in the `AppServiceProvider` class:

```php
<?php
namespace App\Providers;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot(): void
    {
        Request::setTrustedProxies(
            ['192.168.1.1', '192.168.1.2'], // List of trusted proxy IP addresses
            Request::HEADER_X_FORWARDED_ALL // Headers to trust
        );
    }
}
```

In this example, the `setTrustedProxies` method is used to specify that the proxies with IP addresses `192.168.1.1` and `192.168.1.2` are trusted. The `HEADER_X_FORWARDED_ALL` constant indicates that all `X-Forwarded-*` headers should be trusted.

### Configuring Trusted Headers

You can also configure which headers should be trusted by the application. This is useful if your proxy uses custom headers for forwarding information.

Here is an example of how to configure trusted headers:

```php
use Clicalmani\Foundation\Http\RequestInterface as Request;

Request::setTrustedHeaderNames([
    Request::HEADER_FORWARDED => 'X-Forwarded-For',
    Request::HEADER_CLIENT_IP => 'X-Forwarded-Client-IP',
    Request::HEADER_CLIENT_HOST => 'X-Forwarded-Host',
    Request::HEADER_CLIENT_PROTO => 'X-Forwarded-Proto',
    Request::HEADER_CLIENT_PORT => 'X-Forwarded-Port',
]);
```

In this example, the `setTrustedHeaderNames` method is used to specify custom headers for forwarding information. The application will trust these headers when determining the client's IP address, host, protocol, and port.

Now that trusted proxies and headers are set, we can use a middleware to ensure that the incoming request is trustworthy.

Here is an example of middleware that verify trusted proxies and headers:

```php
namespace App\Http\Middleware;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Http\ResponseInterface;
use Clicalmani\Foundation\Http\RedirectInterface;

class TrustProxies
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Foundation\Http\RequestInterface $request Request object
     * @param \Clicalmani\Foundation\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Foundation\Http\ResponseInterface|\Clicalmani\Foundation\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
    {
        if (FALSE === $request->isTrustworthy()) {
            return $response->setStatus(403); // 403 Forbiden to indicate the request is not trustworthy
        }

        return $next($request, $response);
    }
}
```

## Configuring Trusted Hosts

When your application is behind a proxy or load balancer, you may need to configure trusted hosts to ensure that the request information is correctly interpreted. This is particularly important for applications that rely on the client's host information for security or routing purposes.

### Setting Trusted Hosts

To configure trusted hosts, you can use the `setTrustedHosts` method provided by the `Request` class. This method allows you to specify which hosts are trusted.

Here is an example of how to set trusted hosts in your application:

```php
<?php
<?php
namespace App\Providers;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot(): void
    {
        Request::setTrustedHosts([
            'example.com',
            'subdomain.example.com',
        ]);
    }
}
```

In this example, we use the `setTrustedHosts` method in the `AppServiceProvider` to specify that the hosts `example.com` and `subdomain.example.com` are trusted.

Now that trusted hosts are defined, we can use a middleware to ensure that the incoming request is trusted.

Here is an example of middleware that verify trusted hosts:

```php
namespace App\Http\Middleware;

use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Http\ResponseInterface;
use Clicalmani\Foundation\Http\RedirectInterface;

class TrustHosts
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Foundation\Http\RequestInterface $request Request object
     * @param \Clicalmani\Foundation\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Foundation\Http\ResponseInterface|\Clicalmani\Foundation\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : ResponseInterface|RedirectInterface
    {
        if (FALSE === $request->isTrustworthy()) return $response->forbiden();

        return $next($request, $response);
    }
}
```
