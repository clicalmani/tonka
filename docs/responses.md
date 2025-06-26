- [Introduction](responses.md?id=introduction)
- [Creating Responses in **Tonka**](responses.md?id=creating-responses-in-tonka)
    - [Response Object](responses.md?id=response-object)
        - [The `header` Method](responses.md?id=the-header-method)
        - [The `status` Method](responses.md?id=the-status-method)
        - [The `json` Method](responses.md?id=the-json-method)
        - [The `view` Method](responses.md?id=the-view-method)
        - [The `cookie` Method](responses.md?id=the-cookie-method)
    - [File Streaming](responses.md?id=file-streaming)
    - [Send Response Status](responses.md?id=send-response-status)
    - [Middleware Response](responses.md?id=middleware-response)
    - [Redirects](responses.md?id=redirects)

<h1>HTTP Responses</h1>

## Introduction

HTTP responses are standardized replies from a server to a client's request made to the server. They consist of a status line, headers, and an optional body. The status line includes the HTTP version, a status code, and a reason phrase.

Here are common HTTP status codes:

- **200 OK**: The request has succeeded.
- **201 Created**: The request has been fulfilled and has resulted in a new resource being created.
- **400 Bad Request**: The server could not understand the request due to invalid syntax.
- **401 Unauthorized**: The client must authenticate itself to get the requested response.
- **403 Forbidden**: The client does not have access rights to the content.
- **404 Not Found**: The server can not find the requested resource.
- **500 Internal Server Error**: The server has encountered a situation it doesn't know how to handle.

Here is an exemple of an HTTP response:

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
    "message": "Success",
    "data": {
        "id": 1,
        "name": "Example"
    }
}
```

## Creating Responses in **Tonka**

To create an HTTP response in the **Tonka** PHP Framework, you can use the `Response` facade or the response `response` helper function provided by the framework. Below is an example of how to create and return a text response:

```php
use Clicalmani\Foundation\Support\Facades\Response;

Route::get('/', function() {
    return Response::send("This is a plain text response");
});

Route::get('/', function() {
    return response()->send("This is a plain text response");
});
```

### Response Object

The `response` helper function returns an instance of the `Clicalmani\Foundation\Http\Response` class, which provides a variety of methods for building HTTP responses.

#### The `header` Method

To add a header to the response, you can use the `header` method. Here is an example:

```php
Route::get('/header', function() {
    return response('Hello World', 200)
                  ->header('Content-Type', 'text/plain')
                  ->header('X-Custom-Header', 'CustomValue')
                  ->send();
});
```

To set multiple headers at once, you can use the `withHeaders` method. Here is an example:

```php
Route::get('/multiple-headers', function() {
    return response('Hello World', 200)
                  ->withHeaders([
                      'Content-Type' => 'text/plain',
                      'X-Custom-Header-One' => 'ValueOne',
                      'X-Custom-Header-Two' => 'ValueTwo'
                  ])
                  ->send();
});
```

#### The `status` Method

This method is used to set the response status. 

```php
Route::get('/status', function() {
    return response()->status(201)->send('Content');
});
```

#### The `json` Method

To send a JSON response, you can use the `json` method. Here is an example:

```php
Route::get('/json', function() {
    return response()->json([
        'message' => 'Success',
        'data' => [
            'id' => 1,
            'name' => 'Example'
        ]
    ]);
});
```

You can use `success` and `error` methods to send a json status. Here is an exemple:

```php
Route::get('/json', function() {
    return response()->success([
            'id' => 1,
            'name' => 'Example'
        ]);
});

Route::get('/json', function() {
    return response()->error("Error message");
});
```

You can do the same by sending the following data:

```php
Route::get('/json', function() {
    return response()->json([
        'success' => true,
        'data' => [
            'id' => 1,
            'name' => 'Example'
        ]
    ]);
});

Route::get('/json', function() {
    return response()->error([
        'success' => false,
        'data' => 'Error message'
    ]);
});
```

#### The `view` Method

To return a view as a response, you can use the `view` method. Here is an example:

```php
Route::get('/view', function() {
    return response()->view('welcome', ['name' => 'Example']);
});
```

#### The `cookie` Method

To add a cookie to the response, you can use the `cookie` method. Here is an example:

```php
Route::get('/cookie', function() {
    return response('Hello World', 200)
                  ->cookie('name', 'value', 60)
                  ->send();
});
```

To expire a cookie before sending it with a response, you can call the `removeCookie` method. Here is an example:

```php
Route::get('/expire-cookie', function() {
    return response('Cookie expired', 200)
                  ->deleteCookie('name')
                  ->send();
});
```

### File Streaming

To return a file as a response, you can use the `sendFile` method. Here is an example:

```php
Route::get('/download', function() {
    return response()->sendFile(storage_path('/example.pdf'));
});
```

You can also specify the file name that the user will see when downloading the file:

```php
Route::get('/download', function() {
    return response()->sendFile(storage_path('/example.pdf'), 'custom_name.pdf');
});
```

If you want to display the file inline in the browser instead of downloading it, you can use the `stream` method:

```php
Route::get('/file/:filename', function(string $filename) {
    return response()
            ->header('Content-Disposition', "inline; filename=$filename")
            ->stream(storage_path('example.pdf'));
});
```

By default the streaming is manage internally to avoid memory overwelming. The provided code snippet demonstrates how to stream a large file using **Tonka**. This approach helps to avoid overwhelming the server's memory by reading and sending the file in small chunks.

> To manually manage the chunk size of a file being streamed, you can use the `Range` header. This allows you to specify the byte range of the file to be sent in the response.

### Send Response Status

To send a response status you can use the specific status method directly on the response object. Here is an example:

```php
Route::get('/status', function() {
    return response()->notFound();
});

Route::get('/status', function() {
    return response()->forbiden();
});

Route::get('/status', function() {
    return response()->unauthorized();
});

Route::get('/status', function() {
    return response()->internalServerError();
});
```

### Middleware Response

To handle responses within middleware, you can manipulate the response before it is sent to the client. Here is an example of a middleware that adds a custom header to every response:

```php
namespace App\Http\Middleware;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\RequestInterface as Request;
use Clicalmani\Foundation\Http\ResponseInterface;
use Clicalmani\Foundation\Http\RedirectInterface;

class AddCustomHeader extends Middleware
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
        $response->header('X-Custom-Header', 'CustomValue');

        // ...

        return $next($request);
    }
}
```

This middleware will add the `X-Custom-Header` to every response sent by the application.

### Redirects

To redirect a user to a different URL, you can use the `redirect` method. The `Clicalmani\Psr\Response` class contains proper response status to redirect the user to another URL.

```php
Route::get('/home', function() {
    return response()->redirect('/dashboard');
});
```

You can also redirect to a named route:

```php
Route::get('/home', function() {
    return response()->redirect()->route('dashboard', $param1, $apram2, ...);
});
```

To redirect with a flash message, you can use the `with` method:

```php
Route::get('/home', function() {
    return redirect('/dashboard')->with('status', 'Profile updated!');
});
```

To redirect back to the previous URL, you can use the `back` method:

```php
Route::get('/form', function() {
    return redirect()->back();
});
```

You can also use the `back` helpers to redirect back:

```php
Route::post('/form', function() {
    return back();
});
```