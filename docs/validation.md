- [Introduction](#introduction)
- [Getting Started](#getting-started)
- [Defining Validation Rules](#defining-validation-rules)
- [Custom Error Messages](#custom-error-messages)
- [Integrating with Application Logic](#integrating-with-application-logic)
- [Extending Validation](#extending-validation)
- [Examples](#examples)
- [API Reference](#api-reference)

## Introduction
The **Tonka** Validation module provides a robust set of tools for validating user input and application data. It offers a flexible API for defining validation rules, customizing error messages, and integrating validation logic seamlessly into your application workflows. With support for common validation scenarios and extensibility for custom rules, the module helps ensure data integrity and improves application security.

## Getting Started
The **Tonka** Validation module provides an intuitive way to validate user input, route parameters, or database attributes using simple rule definitions. This ensures your application receives clean and expected data before further processing.

## Validating Route Parameters
To validate route parameters, you can use several built-in validation rules. Each rule provides a corresponding method that can be chained with the route definition. This ensures that incoming parameters meet your application's requirements before executing any business logic.

For example, you can define validation rules directly on your route:

```php
$route->get('/user/:id', function(int $id) {
    // Controller logic here
})->whereModel('id', \App\Models\User::class);
```

This approach automatically validates the `id` parameter before your route handler is executed. If validation fails, a 404 HTTP error is returned.

### Available Methods

The Validation module offers a variety of methods to define and apply validation rules to your data. You can use the `where` method to specify validation rules for any type of argument. Besides the `where` method, you can use other validation helpers to further constrain your route parameters:

- **whereNumber**: Ensures the parameter is a valid number.
- **whereInt**: Ensures the parameter is an integer.
- **whereFloat**: Ensures the parameter is a valid floating-point number.
- **whereEnum**: Ensures the parameter value matches one of a set of allowed values (an enumeration).
- **whereToken**: Ensures the value is at least the specified minimum (length or number).
- **wherePattern**: Ensures the parameter matches a specific regular expression pattern.
- **guardAgainst**: Allows you to guard against specific values or patterns for a parameter, rejecting requests that match the guarded criteria.
- **whereModel**: Ensures the parameter corresponds to a valid model instance in your database.
- **whereIn**: Ensures the parameter value is within a specific set.

You can chain these methods to create complex validation rules.

### `whereNumber` Method

The `whereNumber` method ensures that a route parameter is a valid number (integer or float). This is useful for validating IDs, quantities, or any numeric input.

**Usage Example:**

```php
$route->get('/product/:price', function($price) {
    // $price is guaranteed to be a number
})->whereNumber('price');
```

If the parameter does not pass the numeric check, a 404 error response is returned.

### `whereInt` Method

The `whereInt` method validates that a route parameter is an integer. This is particularly useful for IDs or any value that must be a whole number.

**Usage Example:**

```php
$route->get('/order/:orderId', function($orderId) {
    // $orderId is guaranteed to be an integer
})->whereInt('orderId');
```

If the parameter is not a valid integer, a 404 error response is returned.

### `whereFloat` Method

The `whereFloat` method checks that a route parameter is a valid floating-point number. This is useful for validating prices, measurements, or any value that may contain decimals.

**Usage Example:**

```php
$route->get('/measurement/:value', function($value) {
    // $value is guaranteed to be a float
})->whereFloat('value');
```

If the parameter is not a valid float, a 404 error response is returned.

### `whereEnum` Method

The `whereEnum` method validates that a route parameter matches one of a predefined set of allowed values. This is useful for parameters that should only accept specific options, such as status codes or categories.

**Usage Example:**

```php
$route->get('/status/:state', function($state) {
    // $state is guaranteed to be one of the allowed values
})->whereEnum('state', ['active', 'inactive', 'pending']);
```

If the parameter does not match any of the allowed values, a 404 error response is returned.

### `whereIn` Method

The `whereIn` method ensures that a route parameter's value exists within a given array of acceptable values. This is similar to `whereEnum` and is often used for validating IDs or codes against a known list.

**Usage Example:**

```php
$route->get('/category/:id', function($id) {
    // $id is guaranteed to be in the provided list
})->whereIn('id', [1, 2, 3, 4]);
```

If the parameter value is not found in the list, a 404 error response is returned.

### `whereToken` Method

The `whereToken` method validates that a route parameter matches an API token. This method ensures the parameter consists of a valid token.

**Usage Example:**

```php
$route->get('/session/:token', function($token) {
    // $token is guaranteed to be a valid token string
})->whereToken('token');
```

If the parameter does not conform to the expected token format, a 404 error response is returned.

### `wherePattern` Method

The `wherePattern` method validates that a route parameter matches a given regular expression pattern. This is useful for enforcing specific formats, such as UUIDs, custom codes, or date strings.

**Usage Example:**

```php
$route->get('/invoice/:code', function($code) {
    // $code matches the specified pattern
})->wherePattern('code', '/^INV-\d{6}$/');
```

!> **Note:** The regular expression pattern provided to `wherePattern` must not contain the `|` character, as it is reserved and used internally by the validation module.

If the parameter does not match the provided pattern, a 404 error response is returnedthe framework will automatically return a 404 error response.

### `guardAgainst` Method

The `guardAgainst` method allows you to explicitly reject certain values or patterns for a route parameter. This is useful when you want to prevent specific inputs from being accepted by your route.

**Usage Example (Range):**

```php
$route->get('/age/:years', function($years) {
    // $years is guaranteed to be between 18 and 65
})->guardAgainst('years', fn(string $years) => in_array($years, range(18, 65)));
```

If the parameter fails the guard check, a 404 error response is returned.

### `whereModel` Method

The `whereModel` method validates that a route parameter corresponds to an existing model record in your database. This is commonly used for resource routes where the parameter should match a primary key or unique identifier of a model.

**Usage Example:**

```php
$route->get('/user/:id', function($user) {
    // $user is an instance of \App\Models\User if found
})->whereModel('id', \App\Models\User::class);
```

If the parameter does not match any record in the specified model, a 404 error response is returned. When validation passes, the resolved model instance is injected into your route handler.

## Integrating with Application Logic

The Tonka Validation module is designed to integrate seamlessly with your application's workflow. You can apply validation rules at various points in your application, such as within controllers, routes, or database entities, to ensure that data is validated before any critical operations are performed.

## Validating User Inputs

User inputs can be validated by specifying validation rules through the `AsValidator` attribute on controller actions. This approach allows you to define validation logic directly alongside your controller methods, ensuring that incoming request data meets your application's requirements before any business logic is executed.

### Using the `AsValidator` Attribute

To validate user input, annotate your controller action with the `AsValidator` attribute and provide the validation rules as parameters values. The framework will automatically validate the request data against these rules before invoking your action.

**Example:**

```php
use Clicalmani\Validation\AsValidator;

class UserController
{
    #[AsValidator(
        email: 'required|email',
        password: 'required|min:8',
    )]
    public function register(Request $request)
    {
        // $request->email and $request->password are validated
        // Proceed with registration logic
    }
}
```

If validation fails, a `ValidationException` is thrown with an appropriate error message, and your controller action will not be executed.

## Validating Entity Attributes

You can validate entity attributes by using the `Validate` attribute directly on the entity class property. The `Validate` attribute accepts a validation pattern as its parameter, allowing you to enforce rules such as required fields, formats, or custom constraints at the entity level.

**Example:**

```php
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Validate;

class UserEntity extends Entity
{
    #[Property(
        length: 191,
        nullable: false
    ), Validate('required|email|unique:users')]
    public VarChar $email;

    #[Property(
        length: 255
    ), Validate('required|password|min:8|confirm:1|hash:1')]
    public VarChar $password;
}
```

When the entity is processed (e.g., during persistence or form handling), **Tonka** will automatically validate the attributes according to the specified patterns. If validation fails, appropriate error messages will be generated and the operation will be halted.

## Creating Custom Validation Rules

You can extend the **Tonka** Validation module by defining your own custom validation rules. To do this, create a new class that extends the `Rule` base class and implement the `validate` method with your custom logic.

**Example: Custom Rule**

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
}
```

### Register the Custom Validator Rule

To register the custom rule, you need to add it to the `custom_rules` list in `app\Http\Kernel.php` file:

```php
<?php

namespace App\Http;

class Kernel
{
    protected $custom_rules = [
        \App\Validators\PhoneNumberValidato::class,
    ];
}
```

Here is an example of how to use the custom rule in your validation logic:

```php
Route::post('/user/:phone', function (Request $request) {
    // ...
})->where('phone', 'required|phone|pattern:[0-9]{8}');
```

Custom rules allow you to encapsulate complex or application-specific validation logic and reuse it throughout your application.

### Customizing Error Message

To customize the error message for a validation rule, you can implement the `message` method on your custom validation rule class. This method should return the error message you want to display when validation fails.

**Example: Custom Rule with Custom Message**

```php
use Clicalmani\Validation\Rule;

class Uppercase extends Rule
{
    protected string $argument = 'uppercase';

    public function validate(mixed &$value) : bool
    {
        return strtoupper($value) === $value;
    }

    public function message() : string
    {
        return "The {$this->parameter} must be uppercase.";
    }
}
```

To customize the error message for a built-in validation rule, you can override the rule class by running the following command:

```bash
php tonka make:validator RuleName --argument=argument-value --override=built-in-rule-argument
```

Replace `RuleName` with the name of the validator rule. This will generate a new rule class in your application where you can modify the `message` method to return your custom error message.

**Example: Overriding Built-in Rule**

```bash
php tonka make:validator EmailValidator --argument=email --override=email
```

Edit the generated `EmailValidator` rule class and implement the `message` method as needed.

```php
<?php

namespace App\Validators;

use Clicalmani\Validation\Rules\EmailValidator as Base;

class EmailValidator extends Base
{
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return sprintf("The %s field must be a valid email address.", $this->parameter);
    }
}
```

## Integrating with Application Logic

The Tonka Validation module is designed to integrate seamlessly with your application's workflow. You can apply validation rules at various points in your application, such as within controllers, middleware, or service classes, to ensure that data is validated before any critical operations are performed.

### Example 1: Validating a Registration Form

```php
use Tonka\Validation\AsValidator;

class AuthController
{
    #[AsValidator(
        username: 'required|alpha_num|min:3|max:20',
        email: 'required|email|unique:users',
        password: 'required|min:8|confirm:1',
    )]
    public function register($request)
    {
        // Registration logic here
    }
}
```

### Example 2: Validating Route Parameters

```php
$route->get('/product/:id', function($id) {
    // $id is validated as an integer and must exist in the products table
})->whereModel('id', \App\Models\Product::class);
```

### Built-in Validation Rules

| Rule Name      | Description                                                      | Options                 |
|----------------|------------------------------------------------------------------|-------------------------------------|
| `required`     | Ensures the value is present and not empty                       | None                                |
| `nullable`     | Ensures the value can be empty                       | None                                |
| `sometimes`     | Ensures the value can be sometimes empty                       | None                                |
| `email`        | Validates that the value is a valid email address                | `unique:model-name-kabab-case` model to check against `confirm:0-or-1` checks a confirm attribute `attr:attribute-name` table attribute name                               |
| `min`          | Checks that the value meets a minimum length or value            | `min:<number>`                      |
| `max`          | Checks that the value does not exceed a maximum length or value  | `max:<number>`                      |
| `alpha`        | Ensures the value contains only alphabetic characters            | None                                |
| `alpha_num`    | Ensures the value contains only alphanumeric characters          | None                                |
| `numeric`      | Ensures the value is numeric (integer or float)                  | None                                |
| `integer`      | Ensures the value is an integer                                  | None                                |
| `float`        | Ensures the value is a floating-point number                     | None                                |
| `boolean`      | Ensures the value is a boolean                                   | None                                |
| `in`           | Checks that the value is within a set of allowed values          | `in:value1,value2,...`              |
| `not_in`       | Checks that the value is not within a set of disallowed values   | `not_in:value1,value2,...`          |
| `unique`       | Ensures the value is unique in a specified database table        | `unique:<table>[,<column>]`         |
| `exists`       | Ensures the value exists in a specified database table           | `exists:<table>[,<column>]`         |
| `pattern`      | Validates the value against a regular expression                 | `pattern:<regex>`                   |
| `confirmed`    | Ensures the value matches a confirmation field                   | None                                |
| `date`         | Ensures the value is a valid date                                | None                                |
| `before`       | Checks that the date is before a specified date                  | `before:<date>`                     |
| `after`        | Checks that the date is after a specified date                   | `after:<date>`                      |
| `url`          | Ensures the value is a valid URL                                 | None                                |
| `json`         | Ensures the value is a valid JSON string                         | None                                |
| `array`        | Ensures the value is an array                                    | None                                |
| `file`         | Ensures the value is a valid file upload                         | None                                |
| `image`        | Ensures the file is an image                                     | None                                |
| `mimes`        | Checks file MIME types                                           | `mimes:jpg,png,pdf,...`             |
| `size`         | Checks file or string size                                       | `size:<number>`                     |

> **Note:** Some rules accept additional options or parameters, as shown in the table.
