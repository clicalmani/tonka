- [Getting Started](#getting-started)
- [Tonka API Testing Strategy](#tonka-api-testing-strategy)
- [Tonka Testing Workflow](#tonka-testing-workflow)
- [Using Fake Data](#using-fake-data)
- [Test Automation](#test-automation)
    - [Using Test Sequences](#using-test-sequences)
    - [Specifying Multiple Sequences with Array Dot Notation](#specifying-multiple-sequences-with-array-dot-notation)
- [Using Test Sequences](#setting-request-header)
- [Adding Multiple Headers](#adding-multiple-headers)
- [Setting User](#setting-user)
- [Adding URL Hash](#adding-url-hash)


## Getting Started
**Tonka** testing includes API testing using the console, where testers send smilated HTTP requests directly from the command line to verify API endpoints. It enables quick execution of `GET`, `POST`, `PATCH`, `PUT`, and `DELETE` operations, allowing inspection of responses and validation of status codes or payloads. This method supports rapid prototyping, debugging, and simple automation of API checks without requiring a full-featured testing framework.

## Tonka API Testing Strategy

Each API endpoint is tested for:

- **Success scenarios:** Verifying correct responses for valid requests.
- **Failure scenarios:** Ensuring proper error messages and status codes for invalid input or unauthorized access.
- **Edge cases:** Testing boundary conditions, such as empty payloads or maximum field lengths.
- **Authorization:** Confirming endpoints enforce authentication and permissions as required.
- **Data validation:** Checking that input validation rules are enforced and appropriate errors are returned.

This thorough coverage ensures the API behaves as expected under various conditions.

## Tonka Testing Workflow

**Tonka** testing consists of four main steps:

1. **Inherit the HasTest trait:**  
    Ensure the controller you want to test uses the `HasTest` trait to enable testing capabilities.

2. **Generate the test class:**  
    Use the `make:test` command to create a dedicated test file for your controller.

3. **Write the test:**  
    Use the `test` method of the 'test class' to write your test.

4. **Run the test:**  
    Execute the `test` command to run the tests for your controller and review the results in the console.

### Inheriting the HasTest Trait

To enable testing for a controller, add the `HasTest` trait to your controller class. This trait provides the necessary methods and hooks for Tonka's testing system.

Example:

```php
<?php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Acme\Controller;
use Clicalmani\Foundation\Traits\HasTest;

class UserController extends Controller
{
    use HasTest;

    // Controller methods...
}
```

Including the `HasTest` trait ensures your controller is compatible with **Tonka**'s automated test generation and execution.

### Generating the Test Class

To generate a test class for your controller, use the following command in your terminal:

```bash
php tonka make:test UserController
```

This will create a new file named `UserControllerTest.php` in the `tests/Controller` directory. The generated test class will include stub methods for each action in your controller, allowing you to define test data and assertions for each endpoint.

Example structure of the generated test class:

```php
<?php
namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;
use App\Http\Controllers\UserController;

class UserControllerTest extends TestController
{
    protected $controller = UserController::class;

    public function index(): array
    {
        return [
            // Provide parameters for the index action
        ];
    }

    public function store(): array
    {
        return [
            // Provide parameters for the store action
        ];
    }

    // Additional methods for show, update, destroy...

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

You can now customize each method to provide the necessary input data and assertions for your tests:

```php
<?php
namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;
use App\Http\Controllers\UserController;

class UserControllerTest extends TestController
{
    public function store(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john.doe@exemple.com'
        ];
    }
}
```

### Writing the Test
You may call the `test` method statically by specifying a method name as its unique argument in the controller test class `test` method:

```php
<?php
/**
 * Test method
 * 
 * @return void 
 */
public static function test() : void
{
    UserController::test('store')
        ->count(1)
        ->make();
}
```

### Running the Test
Each test can be executed by running the `test` command. You may optinally specify a controller using the `--controller` option:

```bash
php tonka test --controller=UserController
```

After running this command, the test suite will invoke the specify method of the controller. The results will be displayed in the console along with any relevant error messages or output. This feedback helps you quickly identify issues and verify that your API endpoints are functioning as expected.

## Using Fake Data
To simplify test data generation, you can use the `Clicalmani\Database\Faker` class to create fake data for your API tests. This approach helps ensure your tests are robust and not dependent on hardcoded values.

Example of using fake data in a test method:

```php
<?php
namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;

class UserControllerTest extends TestController
{
    public function store(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail
        ];
    }
}
```

This method generates a random name and email address each time the test runs, making your tests more dynamic and realistic.

**Available Faker Methods**          
Here are some commonly used methods provided by the `Clicalmani\Database\Faker` class for generating fake data in your tests:

| Method                | Description                                 | Example Output                |
|-----------------------|---------------------------------------------|-------------------------------|
| `$this->faker->name()`        | Generates a random full name                  | `John Doe`                    |
| `$this->faker->firstName()`   | Generates a random first name                | `Jane`                        |
| `$this->faker->lastName()`    | Generates a random last name                 | `Smith`                       |
| `$this->faker->email`         | Generates a random email address             | `jane.smith@example.com`      |
| `$this->faker->unique()->safeEmail` | Generates a unique, safe email address      | `unique.user@example.com`     |
| `$this->faker->unique()->safeUserName`      | Generates a random username                  | `cooluser123`                 |
| `$this->faker->phone()`   | Generates a random phone number              | `+1-555-123-4567`             |
| `$this->faker->address()`       | Generates a random address                   | `123 Main St, City, Country`  |
| `$this->faker->city()`          | Generates a random city name                 | `Springfield`                 |
| `$this->faker->country()`       | Generates a random country name              | `Canada`                      |
| `$this->faker->date()`        | Generates a random date                      | `2024-06-01`                  |
| `$this->faker->uuid`          | Generates a random UUID                      | `550e8400-e29b-41d4-a716-...` |
| `$this->faker->num()`| Generates a random number                    | `12345`                       |
| `$this->faker->word()`          | Generates a random word                      | `example`                     |
| `$this->faker->sentence()`    | Generates a random sentence                  | `This is a fake sentence.`    |

> **Note:** The actual available methods may vary depending on the version of the Faker library included in your project. Refer to the official documentation for a complete list.

## Test Automation

### Using Test Sequences

The stub method in your test class enables you to define a unique sequence of test data. By leveraging the `Clicalmani\Database\Factory\Sequence` class, you can send multiple sets of test data sequentially to your API endpoints. This is useful for testing how your API handles a series of related requests or for simulating batch operations.

Example of using a sequence in a test method:

```php
<?php
namespace Test\Controllers;

use Clicalmani\Foundation\Test\Controllers\TestController;
use App\Http\Controllers\UserController;
use Clicalmani\Database\Factory\Factory;
use Clicalmani\Database\Factory\Sequence;

class UserControllerTest extends TestController
{
    public function store(): Sequence
    {
        return [
            'name' => null,
            'email' => null
        ];
    }

    public function defaultUsers()
    {
        return $this->state(function(array $default_data) {
            return [
                'name' => new Sequence(
                    $this->faker->name(), 
                    $this->faker->name(), 
                    $this->faker->name()
                ),
                'email' => new Sequence(
                    $this->faker->unique()->safeEamil, 
                    $this->faker->unique()->safeEamil, 
                    $this->faker->unique()->safeEamil
                )
            ];
        });
    }
}
```

When the test runs, each set of data in the sequence will be sent to the API endpoint in order, allowing you to verify consistent behavior across multiple requests.

Here is an exemple:

```php
<?php
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
```
The example demonstrates how to use a sequence of test data in a test class to send multiple, related requests to an API endpoint. This is useful for verifying that your API behaves correctly when handling a batch of similar requests.

### Specifying Multiple Sequences with Array Dot Notation

You can use array dot notation to define and access multiple sequences simultaneously in your test methods. This approach allows you to target nested or grouped data structures efficiently.

For example, if your API expects an array of users, you can define sequences for each user's fields using dot notation:

```php
<?php
public function multipleUsers()
{
    return $this->state(function(array $default_data) {

        $names = [
            ...array_map(fn(string $value) => $this->faker->name(), range(1, 100))
        ];
        $emails = [
            ...array_map(fn(string $value) => $this->faker->unique()->safeEmail, range(1, 100))
        ];

        return [
            'name' => new Sequence(...$names),
            'email' => new Sequence(...$emails)
        ];
    });
}
```

When running the test, each sequence will be applied to the corresponding array element, enabling you to test endpoints that accept arrays or nested objects:

```php
<?php
public static function test() : void
{
    UserController::test('store')
        ->count(100)
        ->multipleUsers()
        ->make();
}
```

This technique streamlines the process of testing batch operations or endpoints that require complex, structured input data.

## Setting Request Header
**Tonka** uses simulated HTTP requests to interact with your controller actions. Sometimes you may need to specify request headers; this is where **Tonka** provides you with virtual headers that can be added by calling the `header` method just before the `make` method.

Example:

```php
<?php
public static function test() : void
{
    UserController::test('store')
        ->count()
        ->header('Authorization', 'Bearer your-token-here')
        ->make();
}
```

This allows you to test endpoints that require authentication tokens, custom content types, or any other HTTP headers. The headers will be included in the simulated request, enabling you to verify how your API handles different header values.

### Adding Multiple Headers

You can add multiple headers to your simulated requests by chaining multiple `header` method calls or by using the `headers` method to set several headers at once.

Example using multiple `header` calls:

```php
<?php
public static function test() : void
{
    UserController::test('store')
        ->count()
        ->header('Authorization', 'Bearer your-token-here')
        ->header('Content-Type', 'application/json')
        ->make();
}
```

Example using the `headers` method:

```php
<?php
public static function test() : void
{
    UserController::test('store')
        ->count()
        ->headers([
            'Authorization' => 'Bearer your-token-here',
            'Content-Type' => 'application/json'
        ])
        ->make();
}
```

This approach allows you to easily test endpoints that require multiple custom headers.

### Setting User

In some cases, your API endpoints may require an authenticated user context. Tonka allows you to simulate requests as a specific user by using the `actingAs` method before calling `make`. This is useful for testing endpoints that enforce authentication or user-specific permissions.

Example:

```php
<?php
public static function test() : void
{
    UserController::test('store')
        ->actingAs(1) // Pass the user ID
        ->make();
}
```

### Passing a Sequence of Users to the `user` Method

**Tonka** allows you to pass a sequence of users to the `user` method, enabling you to simulate requests as different users in a single test run. This is useful for testing endpoints with varying user permissions or roles.

Example:

```php
<?php
public static function test() : void
{
    UserController::test('store')
        ->count(3)
        ->user(new Sequence(1, 2, 3))
        ->make();
}
```

In this example, the test will execute three requests, each authenticated as a different user from the provided sequence. This helps ensure your API behaves correctly for multiple users in batch operations or permission checks.

## Adding URL Hash
Sometimes, you may need to include a URL hash (fragment) in your simulated requests to test how your API handles URL tampering. Tonka allows you to append a hash to the request URL using the `hash` method before calling `make`.

Example:

```php
<?php
public static function test() : void
{
    UserController::test('show')
        ->count()
        ->hash(create_hash_parameters(['user_id' => 1]))
        ->make();
}
```

This will simulate a request to a URL like `/user/show?user_id=1&hash=A76CGHJIOP`. This feature is useful if your application logic or middleware inspects the hash value to prevent route tampering.
