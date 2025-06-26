**Tonka** provides several built-in validators, including `email` validator, `id` validator, `number` validator, `datetime` validator, `enum` validator... These validators can be used anywhere in your application where validation is needed to enhance the reliability and security of your application. You can create your own custom validation and use it on your route or anywhere in your code. We will cover [validation](routing.md?id=validation) later.

```php
Route::get('/user/:id', function (int $id) {
    return response()->send("User ID $id");
})->where('id', 'required|number');

Route::get('/user/:id', function (int $id) {
    return response()->send("User ID $id");
})->where('id', 'required|id|model:user');

Route::get('/user/:email', function (string $email) {
    return response()->send("User Email $email");
})->where('email', 'required|email');

Route::get('/post/:category', function (string $category) {
    return response()->send("Post category $category");
})->where('category', 'required|enum|list:football,handball,bascketball');

Route::get('/post/:date', function (string $date) {
    return response()->send("Post Date $date");
})->where('date', 'required|datetime|format:Y-m-d');
```

If the incoming request does not match the validation constraints, a 404 HTTP response will be returned.

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