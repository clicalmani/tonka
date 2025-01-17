**<h1>CSRF Protection</h1>**

## Introduction
Cross-site request forgeries are a type of malicious exploit whereby unauthorized commands are performed on behalf of an authenticated user. Thankfully, Laravel makes it easy to protect your application from [cross-site request forgery](https://en.wikipedia.org/wiki/Cross-site_request_forgery) (CSRF) attacks.

## An Explanation of the Vulnerability
In case you're not familiar with cross-site request forgeries, let's discuss an example of how this vulnerability can be exploited. Imagine your application has a `/user/email` route that accepts a `POST` request to change the authenticated user's email address. Most likely, this route expects an `email` input field to contain the email address the user want to start using.

Without CSRF protection, a malicious website could create an HTML form that points to your application's `/user/email` route and submits the malicious user's own email address:

```html
<form action="https://your-application.com/user/email" method="POST">
    <input type="email" value="malicious-email@example.com">
</form>
 
<script>
    document.forms[0].submit();
</script>
```
If the malicious website automatically submits the form on page load, the malicious user only needs to trick an unsuspecting user of your application into visiting their website and their email address will be changed in your application .

To prevent this vulnerability, we need to inspect every incoming `POST, PUT, PATCH, or DELETE` request for a secret session value that the malicious application is unable to access.

### Preventing CSRF Requests
Tonka automatically generates a CSRF “token” for each active [user session](session.md) managed by the application. This token makes it possible to verify that the authenticated user is indeed the person who is actually making the requests to the application. Since this token is stored in the user's session and changes each time user session refresh, a malicious application cannot access it.

The current session's CSRF token can be accessed via the request's session or via the `csrf_token()` helper function:

```php
Route::get('/user', function() {
    $token = csrf_token();
});

/**
 * Since anonymous function does not receive a request object
 * we must use a controller for this exemple.
 */
Route::get('/user', [UserController::class, 'show']);

// UserController.php
use Clicalmani\Fundation\Http\Requests\Request;

public function show(Request $request)
{
    $token = $request->session()->token();
}
```