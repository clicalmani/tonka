# Getting Started

- [Introduction](orm.md?id=introduction)
- [Model Class](orm.md?id=model-class)
- [Retrieving Models](orm.md?id=retrieving-models)
- [Building Queries](orm.md?id=building-queries)
- [Collections](orm.md?id=collections)
- [Database Connections](orm.md?id=database-connections)
- [Default Attribute Values](orm.md?id=default-attribute-values)
- [Refreshing Models](orm.md?id=refreshing-models)
- [The `fetch` Method](orm.md?id=the-fetch-method)
- [The `filter` Method](orm.md?id=the-filter-method)
- [The `swap` Method](orm.md?id=the-swap-method)
- [Retrieving a Single Model](orm.md?id=retrieving-a-single-model)
- [Not Found Exceptions](orm.md?id=not-found-exceptions)
- [Retrieving Aggregates](orm.md?id=retrieving-aggregates)
    - [Count](orm.md?id=count)
    - [Max](orm.md?id=max)
    - [Min](orm.md?id=min)
    - [AVG](orm.md?id=avg)
    - [Sum](orm.md?id=sum)
- [Inserting and Updating Models](orm.md?id=inserting-and-updating-models)
    - [Inserting Models](orm.md?id=inserting-models)
    - [Updating Models](orm.md?id=updating-models)
    - [Mass Assignment](orm.md?id=mass-assignment)
    - [Guarding Attributes](orm.md?id=guarding-attributes)
    - [Fillable Attributes](orm.md?id=fillable-attributes)
    - [Bulk Updates](orm.md?id=bulk-updates)
- [Deleting Models](orm.md?id=deleting-models)
    - [Deleting a Single Instance](orm.md?id=deleting-a-single-instance)
    - [Deleting Multiple Instances](orm.md?id=deleting-multiple-instances)
    - [Soft Deleting Models](orm.md?id=soft-deleting-models)
        - [Enabling Soft Deletes](orm.md?id=enabling-soft-deletes)
        - [Querying Soft Deleted Models](orm.md?id=querying-soft-deleted-models)
        - [Restoring Soft Deleted Models](orm.md?id=restoring-soft-deleted-models)
        - [Permanently Deleting Models](orm.md?id=permanently-deleting-models)
- [Examining Model State](orm.md?id=examining-model-state)
    - [`isDirty` Method](orm.md?id=isdirty-method)
    - [`isClean` Method](orm.md?id=isclean-method)
    - [`wasChanged` Method](orm.md?id=waschanged-method)
- [Mass Assignment Exceptions](orm.md?id=mass-assignment-exceptions)
- [Events](orm.md?id=events)
    - [Registering Events](orm.md?id=registering-events)
    - [Available Events](orm.md?id=available-events)
    - [Example: Logging Model Events](orm.md?id=example:-logging-model-events)
    - [Custom Events](orm.md?id=custom-events)
    - [Event Observers](orm.md?id=event-observers)
        - [Creating an Observer](orm.md?id=creating-an-observer)
        - [Registering an Observer](orm.md?id=registering-an-observer)
        - [Observers and Database Transactions](orm.md?id=observers-and-database-transactions)
    - [Muting Events](orm.md?id=muting-events)
    - [Saving a Single Model Without Events](orm.md?id=saving-a-single-model-without-events)
- [Relationships](orm.md?id=relationships)
    - [Defining Relationships](orm.md?id=defining-relationships)
        - [One-to-One](orm.md?id=one-to-one)
        - [One-to-Many](orm.md?id=one-to-many)
        - [Many-to-Many](orm.md?id=many-to-many)
        - [Has One-Through](orm.md?id=has-one-through)
        - [Has Many-Through](orm.md?id=has-many-through)
        - [One-to-One (Polymorphic)](orm.md?id=one-to-one-polymorphic)
        - [One-to-Many (Polymorphic)](orm.md?id=one-to-many-polymorphic)
        - [Many-to-Many (Polymorphic)](orm.md?id=many-to-many-polymorphic)
        - [Custom Polymorphic Types](orm.md?id=custom-polymorphic-types)
    - [Querying Related Models](orm.md?id=querying-related-models)
        - [Model Joins](orm.md?id=model-joins)
        - [The `fetch` Method on Joins](orm.md?id=the-fetch-method-on-joins)
        - [Table Alias](orm.md?id=table-alias)
    - [Model Attributes Validation](orm.md?id=model-attributes-validation)
        - [Defining Validation Rules](orm.md.md?id=defining-validation-rules)
        - [Available Validators](orm.md?id=available-validators)
        - [Validating Model Instances](orm.md?id=validating-model-instances)
        - [Creating a Custom Validator](orm.md?id=creating-a-custom-validator)
            - [Defining a Custom Validator](orm.md?id=defining-a-custom-validator)
            - [Register the Custom Validator](orm.md?id=register-the-custom-validator)
            - [Using the Custom Validator](orm.md?id=using-the-custom-validator)

## Introduction

Tonka ORM is a powerful and flexible Object-Relational Mapping tool designed to simplify database interactions in your applications. By abstracting the complexities of SQL queries, Tonka ORM allows developers to work with database records as if they were regular PHP objects. This not only speeds up development time but also ensures that your codebase remains clean and maintainable. Whether you are building a small project or a large-scale enterprise application, Tonka ORM provides the tools and features necessary to manage your data efficiently and effectively.

## Model Class

A Tonka ORM model class is a representation of a database table within your application. Each model class maps to a specific table in the database, and each instance of the model represents a row within that table. By defining model classes, you can interact with your database using object-oriented principles, making your code more intuitive and easier to manage.

Tonka ORM provides a convenient command-line tool to generate model classes quickly. You can use the `make:model` command to create a new model class. This command will generate a boilerplate model class file in your project, saving you time and effort.

To create a new model class, run the following command in your terminal:

```sh
php tonka make:model ModelName
```

Replace `ModelName` with the desired name of your model. For example, to create a `Product` model, you would run:

```sh
php tonka make:model Product
```

This command will generate a new file named `Product.php` with a basic structure for your model class. You can then customize this file by adding fields and methods as needed.

By using the `make:model` command, you can streamline the process of creating new models and ensure consistency across your project.

Here is an example of a simple model class in Tonka ORM:

```php
<?php
use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    /**
     * Model database table 
     *
     * @var string $table Table name
     */
    protected $table = "users";

    /**
     * Model entity
     * 
     * @var string
     */
    protected string $entity = \Database\Entities\UserEntity::class;

    /**
     * Table primary key(s)
     * Use an array if the key is composed with more than one attributes.
     *
     * @var string|array $primary_keys Table primary key.
     */
    protected $primaryKey = "user_id";

    public function __construct($id = null)
    {
        parent::__construct($id);
    }
}
```

With this model class, you can perform various database operations such as creating, reading, updating, and deleting records without writing raw SQL queries.

## Retrieving Models

Once you have created a model and its associated database table, you are ready to start retrieving data from your database. The model's `all` method will retrieve all of the records from the model's associated database table:

```php
use App\Models\Flight;
 
foreach (Flight::all() as $flight) {
    echo $flight->name;
}
```

### Building Queries

To build queries based on a model in Tonka ORM, you can use the built-in query builder methods provided by the framework. These methods allow you to construct complex SQL queries using a fluent, object-oriented interface.

Here is an example of how to build a query using the `Flight` model class:

```php
<?php
use App\Models\Flight;

$flights = Flight::where('active = ?', [1])
               ->orderBy('name')
               ->top(10)
               ->get();
```

### Collections

As we have seen, methods like `all` and `get` retrieve multiple records from the database. However, these methods don't return a plain PHP array. Instead, an instance of `Clicalmani\Foundation\Collection\Collection` is returned.

```php
$flights = Flight::where('destination = ?', ['Paris'])->fetch();
 
$flights->filter(function (Flight $flight) {
    return $flight->cancelled;
});
```

Since all of Tonka's collection implement PHP's iterable interface, you may loop over collections as if they were an array:

```php
foreach ($flights as $flight) {
    echo $flight->name;
}
```

## Database Connections

Tonka ORM makes it easy to manage database connections in your application. You can configure multiple database connections and switch between them as needed. The configuration for database connections is typically stored in  `config/database.php` configuration file.

Here is the content of `database.php` database configuration file:

```php
return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        // Add other database connections here
    ],
];
```

To use a specific database connection in your model, you can set the `$connection` property:

```php
<?php
use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $table = "users";
    protected $connection = "mysql"; // Specify the connection name

    // Other model properties and methods
}
```

You can also switch the database connection dynamically at runtime:

```php
User::on('sqlite')->get();
```

When you already holding a model instance and still want to switch the connection, you can call the `setConnection` method:

```php
$user = new User;
$user->setConnection('sqlite');
```

## Default Attribute Values

Tonka ORM allows you to define default values for your model attributes. This can be useful when you want to ensure that certain fields always have a specific value when a new model instance is created.

To define default attribute values, you can use the `$attributes` property in your model class. This property should be an associative array where the keys are the attribute names and the values are the default values.

Here is an example of how to define default attribute values in a model class:

```php
<?php
use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $table = "users";

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'active',
        'role' => 'user',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
    }
}
```

In this example, the `status` attribute will default to `active` and the `role` attribute will default to `user` when a new `User` model instance is created.

You can override these default values by explicitly setting the attribute values when creating a new model instance:

```php
$user = new User;
$user->status = 'inactive';
$user->role = 'admin';
```

!> If those attributes are not explicitly specified when inserting a new record, default values will be used.

## Refreshing Models

Tonka ORM provides a convenient way to refresh model instances with the latest data from the database. This can be useful when you want to ensure that your model instance reflects the most current state of the corresponding database record.

To refresh a model instance, you can use the `refresh` method. This method will reload the model's attributes from the database, discarding any changes that have not been saved.

Here is an example of how to use the `refresh` method:

```php
$user = User::find(1);

// Make some changes to the model
$user->name = 'New Name';

// Refresh the model instance with the latest data from the database
$user->refresh();
```

After calling the `refresh` method, the `$user` instance will be updated with the latest data from the database, and any unsaved changes will be discarded.

This feature is particularly useful in scenarios where multiple processes or users may be updating the same database records concurrently, ensuring that your model instances always reflect the most current state of the data.

## The `fetch` Method

The `fetch` method in Tonka ORM allows you to retrieve records from the database as the model objects. This method is useful when you are planning to use some model properties or method on each record. The fetch method returns a collection to allows you to easily manage the records.

Here is an example of how to use the `fetch` method:

```php
use App\Models\User;

$admin_users = User::where()
                ->fetch()
                ->filter(fn(User $user) => $user->isAdmin());
```

You can pass a model class to the `fetch` method when using joins:

```php
$user = User::where('status = ?', ['active'])
            ->joinLeft(Order::class)
            ->fetch(Order::class)
            ->filter(fn(Order $order) => $order->shipped());
```

This query retrieves the orders of all active users. By default if none is specified the users will be returned.

## The `filter` Method

The `filter` method in Tonka ORM allows you to filter the table records based on request parameters. This method is useful when you need to retrieve some records based on some request parameters.

Here is an example of how to use the `filter` method:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Response;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function index()
    {
        return response()->json(
            User::filter()
        );
    }
}
```

The users returned in the `index` method will be filtered based on the request. Let's assume the request provided the `is_admin` field, a query will be automatically generated based on the value provided by the request.

The `filter` method accepts two optional arguments. The first argument is an array of fields to be excluded when generating the database query, the second argument is an array whose keys can be any of the following ones `order_by`, `offset` and `limit` that allow to order the query results or to limit the maximum number of results to be returned.

Here is an exemple with all the arguments set:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Response;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function index()
    {
        return response()->json(
            User::filter(['password'], [
                'order_by' => 'name',
                'offset' => 0,
                'limit' => 10
            ])
        );
    }
}
```

In this exemple all fields can be filtered except the `password` field. The result will be ordered by user name and the maximum number of records to be returned is 10.

!> You can rely on the `filter` method to filter your table records, as it will safely sanitize the request parameters before checking them against the table to unsure that such attributes exists. You can rely on attribute validators to validate input fields before they reach the `filter` metod.

## The `swap` Method

The `swap` method is smilar to the `filter` method as it will use the provided request parameters to create a new record or update and existing one.

Here is an example of how to use the `swap` method:

```php
<?php

namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\Response;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Model\User $user
     * @return \Clicalmani\Foundation\Http\Response\JsonResponse
     */
    public function store(User $user)
    {
        try {
            $user->swap();
            $user->save();

            return response()->success();
        } catch (\PDOException $e) {
            return response()->error($e->getMessage());
        }
    }
}
```

After calling the `save` method, all the values will be saved to the database.

This method simplifies the process of specifing the attribute values when creating or updating a record.

!> The `swap` method is safe, as it sanitize the request parameters before checking them against the table. Each parameter value can be validated with attribute validator specified on the method.

## Retrieving a Single Model

To retrieve a single model instance from the database, you can use the `find` method. This method retrieves a record by its primary key:

```php
$user = User::find(1);
```

In this example, the `find` method retrieves the user with the primary key value of `1`. If no record is found, the method returns `null`.

You can also use the `first` method to retrieve the first record that matches a given query:

```php
$user = User::where('email = ?', ['example@example.com'])->first();
```

If no record is found, the `first` method returns `null`.

Both `find` and `first` methods allow you to quickly retrieve single model instances based on specific criteria.

## Handling Missing Models

Sometimes you may wish to perform some other action if no results are found. The `findOr` and `firstOr` methods will return a single model instance or, if no results are found, execute the given closure. The value returned by the closure will be considered the result of the method:

```php
$user = User::findOr(1, function () {
    $user = new User;
    $user->email = 'exemple@example.com';
    // ...
    $user->save();
    return $user;
});
```

In this example, if no user with the primary key value of `1` is found, a new `User` instance with the email `example@example.com` will be returned.

Similarly, you can use the `firstOr` method to handle missing models when querying based on specific criteria:

```php
$user = User::where('email = ?', ['example@example.com'])->firstOr(function () {
    $user = new User;
    $user->email = 'example@example.com';
    // ...
    $user->save();
    return $user;
});
```

If no user with the specified email is found, a new `User` instance will be returned.

## Not Found Exceptions

Tonka ORM provides a way to handle cases where a model is not found by throwing exceptions. This can be useful when you want to ensure that your application handles missing records gracefully.

To throw an exception when a model is not found, you can use the `findOrFail` and `firstOrFail` methods. These methods will retrieve a model instance or throw a `ModelNotFoundException` if no record is found.

Here is an example of how to use the `findOrFail` method:

```php
use Clicalmani\Foundation\Exceptions\ModelNotFoundException;

try {
    $user = User::findOrFail(1);
} catch (ModelNotFoundException $e) {
    // Handle the exception, e.g., return a 404 response
    echo 'User not found';
}
```

In this example, if no user with the primary key value of `1` is found, a `ModelNotFoundException` will be thrown, and you can handle the exception accordingly.

Similarly, you can use the `firstOrFail` method to throw an exception when querying based on specific criteria:

```php
use Clicalmani\Foundation\Exceptions\ModelNotFoundException;

try {
    $user = User::where('email = ?', ['example@example.com'])->firstOrFail();
} catch (ModelNotFoundException $e) {
    // Handle the exception, e.g., return a 404 response
    echo 'User not found';
}
```

## Retrieving Aggregates

Tonka ORM provides several methods to retrieve aggregate values from your database, such as `count`, `max`, `min`, `avg`, and `sum`. These methods allow you to perform calculations on your data without retrieving all the records.

### Count

The `count` method returns the total number of records that match a given query:

```php
$activeUserCount = User::where('status = ?', ['active'])->count();
```

### Max

The `max` method returns the maximum value of a given column:

```php
$maxAge = User::where('status = ?', ['active'])->max('age');
```

### Min

The `min` method returns the minimum value of a given column:

```php
$minAge = User::where('status = ?', ['active'])->min('age');
```

### Avg

The `avg` method returns the average value of a given column:

```php
$averageAge = User::where('status = ?', ['active'])->avg('age');
```

### Sum

The `sum` method returns the sum of a given column:

```php
$totalPoints = User::where('status = ?', ['active'])->sum('points');
```

These aggregate methods provide a convenient way to perform calculations on your data without having to retrieve and process all the records manually.

## Inserting and Updating Models

Tonka ORM makes it easy to insert new records into the database and update existing ones. You can use the `save` method to persist model instances to the database.

### Inserting Models

To insert a new record into the database, you can create a new instance of the model, set its attributes, and call the `save` method:

```php
$user = new User;
$user->name = 'John Doe';
$user->email = 'john.doe@example.com';
$user->save();
```

This will insert a new record into the `users` table with the specified attributes.

### Updating Models

To update an existing record, you can retrieve the model instance, modify its attributes, and call the `save` method:

```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();
```

This will update the `name` attribute of the user with the primary key value of `1`.

### Mass Assignment

Tonka ORM also supports mass assignment, allowing you to set multiple attributes at once using an associative array:

```php
$user = new User;
$user->fill([
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
]);
$user->save();
```

Alternatively, you may use the `create` method to "save" a new model using a single PHP statement. The inserted model instance will be returned to you by the create method:

```php
use App\Models\User;
 
$user = User::create([
    'name' => 'John Doe',
]);
```

You can also use mass assignment when updating an existing model:

```php
$user = User::find(1);
$user->fill([
    'name' => 'Jane Doe',
    'email' => 'jane.doe@example.com',
]);
$user->save();
```

### Guarding Attributes

To protect against mass assignment vulnerabilities, you can define a `$guarded` property in your model class. This property should be an array of attribute names that you do not want to be mass assignable.

Here is an example of how to define guarded attributes in a model class:

```php
<?php
use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $table = "users";

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['password', 'is_admin'];

    public function __construct($id = null)
    {
        parent::__construct($id);
    }
}
```

In this example, the `password` and `is_admin` attributes are guarded and cannot be mass assigned.

### Fillable Attributes

Alternatively, you can define a `$fillable` property to specify which attributes should be mass assignable. This property should be an array of attribute names that you want to allow for mass assignment.

Here is an example of how to define fillable attributes in a model class:

```php
<?php
use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email'];

    public function __construct($id = null)
    {
        parent::__construct($id);
    }
}
```

In this example, only the `name` and `email` attributes are mass assignable.

By using the `$guarded` or `$fillable` properties, you can control which attributes can be mass assigned, helping to prevent mass assignment vulnerabilities in your application.

### Bulk Updates

To update multiple records at once, you can use the `update` method with a query:

```php
User::where('status', 'inactive')->update(['status' => 'active']);
```

This will update the `status` attribute of all inactive users to `active`.

### Deleting Models

#### Deleting a Single Instance

To delete a model instance from the database, you can use the `delete` method:

```php
$user = User::find(1);
$user->delete();
```

#### Deleting Multiple Instance

You can also delete multiple records using a query:

```php
User::where('status = ?', ['inactive'])->forceDelete();
```

You can truncate the model table by calling the destroy method:

```php
User::Destroy();
```

!> The `forceDelete` and the `Destroy` methods do not trigger any model event and will also delete soft deleted records.

#### Soft Deleting Models

In addition to actually removing records from your database, Tonka ORM can also "soft delete" models. When models are soft deleted, they are not actually removed from your database. Instead, a `deleted_at` attribute is set on the model indicating the date and time at which the model was "deleted". To enable soft deletes for a model, add the `SoftDelete` trait to the model:

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

## Examining Model State

Tonka ORM provides several methods to examine the internal state of your model and determine how its attributes have changed from when the model was originally retrieved. These methods are `isDirty`, `isClean`, and `wasChanged`.

### `isDirty` Method

The `isDirty` method checks if any attributes have been changed since the model was retrieved or last saved. It returns `true` if any attributes have been modified, otherwise `false`.

Here is an example of how to use the `isDirty` method:

```php
$user = User::find(1);
$user->name = 'New Name';

if ($user->isDirty()) {
    echo 'The user model has been modified.';
}
```

You can also check if specific attributes have been changed by passing the attribute names as arguments:

```php
if ($user->isDirty('name')) {
    echo 'The name attribute has been modified.';
}
```

### `isClean` Method

The `isClean` method is the opposite of `isDirty`. It checks if no attributes have been changed since the model was retrieved or last saved. It returns `true` if no attributes have been modified, otherwise `false`.

Here is an example of how to use the `isClean` method:

```php
$user = User::find(1);

if ($user->isClean()) {
    echo 'The user model has not been modified.';
}
```

You can also check if specific attributes have not been changed by passing the attribute names as arguments:

```php
if ($user->isClean('name')) {
    echo 'The name attribute has not been modified.';
}
```

### `wasChanged` Method

The `wasChanged` method checks if any attributes were changed when the model was last saved. It returns `true` if any attributes were modified, otherwise `false`.

Here is an example of how to use the `wasChanged` method:

```php
$user = User::find(1);
$user->name = 'New Name';
$user->save();

if ($user->wasChanged()) {
    echo 'The user model was modified when it was last saved.';
}
```

You can also check if specific attributes were changed by passing the attribute names as arguments:

```php
if ($user->wasChanged('name')) {
    echo 'The name attribute was modified when the user was last saved.';
}
```

These methods provide a convenient way to track changes to your model's attributes and ensure that your application logic can respond appropriately to those changes.

The `getOriginal` method returns an array containing the original attributes of the model regardless of any changes to the model since it was retrieved. If needed, you may pass a specific attribute name to get the original value of a particular attribute:

```php
$user = User::find(1);
$user->name = 'New Name';

// Get all original attributes
$originalAttributes = $user->getOriginal();

// Get the original value of the 'name' attribute
$originalName = $user->getOriginal('name');
```

In this example, the `$originalAttributes` variable will contain an array of the user's original attributes, and the `$originalName` variable will contain the original value of the `name` attribute before it was changed to 'New Name'.

## Mass Assignment Exceptions

By default, attributes that are not included in the $fillable array are silently discarded when performing mass-assignment operations. In production, this is expected behavior; however, during local development it can lead to confusion as to why model changes are not taking effect.

If you wish, you may instruct Tonka to throw an exception when attempting to fill an unfillable attribute by invoking the `preventSilentlyDiscardingAttributes` method. Typically, this method should be invoked in the boot method of your application's AppServiceProvider class:

```php
use Clicalmani\Database\Exceptions\MassAssignmentException;
use Clicalmani\Database\Factory\Models\Model;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Model::preventSilentlyDiscardingAttributes();
    }
}
```

When a mass assignment attempt is made with attributes that are not specified in the `$fillable` property or are listed in the `$guarded` property, a `MassAssignmentException` will be thrown.

Here is an example of how to handle mass assignment exceptions:

```php
use Clicalmani\Foundation\Exceptions\MassAssignmentException;

try {
    $user = new User;
    $user->fill([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'secret', // Assuming 'password' is guarded
    ]);
    $user->save();
} catch (MassAssignmentException $e) {
    // Handle the exception, e.g., log the error or return a response
    echo 'Mass assignment error: ' . $e->getMessage();
}
```

In this example, if the `password` attribute is guarded, a `MassAssignmentException` will be thrown, and you can handle the exception accordingly.

## Events

Tonka ORM provides a powerful event system that allows you to hook into various points in the model's lifecycle. This can be useful for performing actions when certain events occur, such as creating, updating, or deleting a model.

### Registering Events

To register an event listener, you can implement the `booted` method on your model.

Here is an example of how to register an event listener for the `creating` event:

```php
<?php 
namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    /**
     * Model database table 
     *
     * @var string $table Table name
     */
    protected $table = "users";

    protected function booted(): void
    {
        $this->creating(function(User $user) {
            // The logic of your code
        });
    }
}
```

### Available Events

Tonka ORM provides several events that you can listen for:

- `retrieved`: Triggered after a model is retrieved from the database.
- `creating`: Triggered before a model is created.
- `created`: Triggered after a model is created.
- `updating`: Triggered before a model is updated.
- `updated`: Triggered after a model is updated.
- `saving`: Triggered before a model is saved (either creating or updating).
- `saved`: Triggered after a model is saved (either creating or updating).
- `deleting`: Triggered before a model is deleted.
- `deleted`: Triggered after a model is deleted.
- `restoring`: Triggered before a soft-deleted model is restored.
- `restored`: Triggered after a soft-deleted model is restored.

### Example: Logging Model Events

Here is an example of how to log model events using the `booted` method:

```php
<?php 
namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    /**
     * Model database table 
     *
     * @var string $table Table name
     */
    protected $table = "users";

    protected function booted(): void
    {
        $this->creating(function(User $user) {
            Logger::info('Creating user: ' . $user->email);
        });

        $this->updating(function(User $user) {
            Logger::info('Updating user: ' . $user->email);
        });

        $this->deleting(function(User $user) {
            Logger::info('Deleting user: ' . $user->email);
        });
    }
}
```

### Custom Events

In addition to the built-in events, you can also define custom events for your models. To define a custom event, use the `make:model-event` console command:

```sh
php tonka make:model-event-listener ActivateUser
```

After running this command a file `ActivateUser.php` in the `app/Events` directory.

```php
<?php
namespace App\Events;

use Clicalmani\Database\Factory\Models\Events\Event;
use Clicalmani\Database\Events\EventListener;

class ActivateUser extends EventListener
{
    /**
     * Handle event
     * 
     * @param mixed $data Event data
     * @return void
     */
    public function handle(mixed $data) : void
    {
        /** @var \App\Models\User */
        $user = $this->target;

        $user->status = $status;
        $user->save();

        // Fire another custom event
        $user->emit('activated');
    }
}
```

## Event Observers

Observers are classes that group event handling logic for a model, making it easier to manage and maintain.

### Creating an Observer

To create an observer, define a class with methods corresponding to the events you want to handle. Each method should accept the model instance as its parameter.

Here is an example of a `UserObserver` class:

```php
<?php

namespace App\Observers;

use App\Models\User;
use Clicalmani\Database\Events\EventObserver;

class UserObserver extends EventObserver
{
    public function creating(User $user)
    {
        // Perform some action before creating the user
        $user->created_at = now();
    }

    public function updating(User $user)
    {
        // Perform some action before updating the user
        $user->updated_at = now();
    }

    public function deleting(User $user)
    {
        // Perform some action before deleting the user
        Logger::info('Deleting user: ' . $user->email);
    }
}
```

### Registering an Observer

To register an observer, $observers property must be defined in the model class:

```php
<?php

namespace App\Models\User;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $observers = [
        \App\Observers\UserObserver::class
    ]
}
```

### Example: Logging User Events

Here is an example of how to log user events using an observer:

```php
<?php

namespace App\Observers;

use App\Models\User;
use Clicalmani\Foundation\Support\Facades\Log;
use Clicalmani\Database\Events\EventObserver;

class UserObserver extends EventObserver
{
    public function creating(User $user)
    {
        Log::info('Creating user: ' . $user->email);
    }

    public function updating(User $user)
    {
        Log::info('Updating user: ' . $user->email);
    }

    public function deleting(User $user)
    {
        Log::info('Deleting user: ' . $user->email);
    }
}
```

By using observers, you can keep your event handling logic organized and maintainable, making it easier to manage complex event-driven behavior in your application.

### Observers and Database Transactions

### Observers and Database Transactions

When working with observers and database transactions, it's important to ensure that your event handling logic is consistent and reliable. Tonka ORM provides a way to handle model events within the context of a database transaction, ensuring that your events are only triggered if the transaction is successful.

Here is an example of how to use observers with database transactions:

```php
use Clicalmani\Foundation\Support\Facades\DB;
use App\Models\User;

DB::transaction(function () {
    $user = new User;
    $user->name = 'John Doe';
    $user->email = 'john.doe@example.com';
    $user->save();
});
```

In this example, the `creating` and `created` events will only be triggered if the transaction is successful. If the transaction fails, the events will not be triggered, ensuring that your event handling logic remains consistent.

## Muting Events

Sometimes you would like to temporarily mute one or more model events, which can be useful when you need to perform operations without triggering event listeners. This can be achieved using the `muteEvents` method.

Here is an example of how to mute events while creating a new user:

```php
use App\Models\User;

$user = new User;
$user->muteEvents(['creating'])
    ->fill([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com'
    ]);
$user->save();
```

In this example, the `creating` event will not be triggered, but `created`, `saving` and `saved` will.

## Saving a Single Model Without Events

Tonka ORM allows you to save a single model instance without triggering any events. This can be useful when you need to perform operations without invoking event listeners.

To save a model without triggering events, you can use the `saveQuietly` method:

```php
$user = User::find(1);
$user->name = 'Jane Doe';
$user->saveQuietly();
```

In this example, the `saving` and `saved` events will not be triggered when the user is saved.

## Relationships

Database tables are often related to one another. For example, a blog post may have many comments or an order could be related to the user who placed it. Tonka ORM makes managing and working with these relationships easy, and supports a variety of common relationships:

- **One-to-One**: A single model is associated with one other model.
- **One-to-Many**: A single model is associated with multiple models.
- **Many-to-Many**: Multiple models are associated with multiple other models.
- **Has One-through**: This relationship indicates that a model is related to another model through a third model. It is typically used to define a one-to-one relationship that is connected via an intermediary model.
- **Has-Many-Through**: This relationship type is used to define a many-to-many relationship that is accessed through an intermediate model. It is useful when you need to work with a relationship that involves three models. For example, if you have a `User` model, a `Role` model, and a `Permission` model, and you want to define a relationship where users have many permissions through roles, you would use a "Has-Many-Through" relationship.
- **One-to-One (Polymorphic)**: This type of relationship allows an entity to belong to more than one other entity on a single association. For example, a `Photo` model might belong to either a `User` model or an `Article` model. This is useful when you have a single model that can be associated with multiple other models in a one-to-one relationship.
- **One-to-Many (Polymorphic)**: This relationship type allows a single model to be associated with multiple models. For example, a comment model can belong to both a post model and a video model. This is useful when you want to reuse the same relationship logic for different models.
- **Many-to-Many (Polymorphic)**: In a many-to-many polymorphic relationship, a model can belong to more than one type of model on a single association. This is useful for scenarios where a model needs to be associated with multiple other models using a single relationship.

## Defining Relationships

Tonka ORM relationships are defined as methods on your model classes. Since relationships also serve as powerful query builders, defining relationships as methods provides powerful method chaining and querying capabilities. For example, we may chain additional query constraints on this posts relationship:

```php
$user = User::find(1);
$posts = $user->posts()->where('published = ?', [true])->get();
```

### One-to-One

A one-to-one relationship is used to define a single association between two models. For example, a `User` model might be associated with a `Profile` model.

### Example

Here is an example of how to define a one-to-one relationship in Tonka ORM:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
```

!> Tonka ORM uses a key logic that gives the table name the suffix `id` for foreign keys and `id` for parent keys. You can use custom key logic by passing the foreign key as the second argument of the `hasOne` method and parent key as the third argument. You may omit the parent key if it is the same as the foreign key.

#### Database Schema

To implement this relationship in your database, you would typically have a foreign key in the `Profile` table that references the `User` table:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE profiles (
    id INT PRIMARY KEY,
    user_id INT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

This schema ensures that each profile is linked to a specific user, enforcing the one-to-one relationship at the database level.

#### Defining the Inverse of the Relationship

You may need to define the inverse of the relationship, to do it you can use the `belongsTo` method:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### One-to-Many

A one-to-many relationship is used to define a single model that is associated with multiple models. For example, a `Post` model might have many `Comment` models.

#### Example

Here is an example of how to define a one-to-many relationship in Tonka ORM:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

#### Database Schema

To implement this relationship in your database, you would typically have a foreign key in the `Comment` table that references the `Post` table:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    post_id INT,
    content TEXT,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
```

This schema ensures that each comment is linked to a specific post, enforcing the one-to-many relationship at the database level.

#### Defining the Inverse of the Relationship

In a one-to-many relationship, you may need to define the inverse of the relationship to access the parent model from the child model. This is done using the `belongsTo` method.

Here is an example:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Comment extends Model
{
    public function posts()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### Many-to-Many

A many-to-many relationship is used to define multiple models that are associated with multiple other models. For example, a `User` model might belong to many `Role` models, and a `Role` model might belong to many `User` models.

Here is an example of how to define a many-to-many relationship in Tonka ORM:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
```

#### Database Schema

To implement this relationship in your database, you would typically have a pivot table that references both the `User` and `Role` tables:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE role_user (
    user_id INT,
    role_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    PRIMARY KEY (user_id, role_id)
);
```

This schema ensures that each user can be linked to multiple roles and each role can be linked to multiple users, enforcing the many-to-many relationship at the database level.

#### Accessing the Relationship

In your code to access a Many-to-Many relationship, you must establish a join on the table by calling one of the pivot method `pivotLeft`, `pivotRight`, `pivotInner`, `pivotOuter`, `pivotCross`:

Here is an example of how to access users on a role instance:

```php
$role = Role::find(1);
$role->pivotLeft(RoleUser::class, 'role_id', 'id')->users();
```

In this example we pivot the role instance to the left to get users instances. We may do the same by pivoting a user instance to the right to get roles instances and vis-versa.

```php
$user = User::find(1);
$user->pivotRight(RoleUser::class, 'user_id', 'id')->roles();
```

### Has One-Through

A has one-through relationship is used to define a one-to-one relationship that is connected via an intermediary model. For example, a `Supplier` model might be related to a `User` model through an `Order` model.

#### Example

Here is an example of how to define a has one-through relationship:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Supplier extends Model
{
    public function profile()
    {
        return $this->hasOneThrough(Profile::class, User::class);
    }
}
```

#### Database Schema

To implement this relationship in your database, you would typically have foreign keys in the `User` and `Profile` tables that reference the `Supplier` and `User` tables, respectively:

```sql
CREATE TABLE suppliers (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE users (
    id INT PRIMARY KEY,
    supplier_id INT,
    name VARCHAR(255),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE profiles (
    id INT PRIMARY KEY,
    user_id INT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

This schema ensures that each profile is linked to a specific user, and each user is linked to a specific supplier, enforcing the "has one through" relationship at the database level.

#### Accessing the Relationship

To access the relationship, you can use the defined relationship methods on your models. Here is an example of how to access the profile of a supplier through the user model:

```php
$supplier = Supplier::find(1);
$profile = $supplier->profile();
```

### Has Many-Through

A has many-through relationship is used to define a many-to-many relationship that is accessed through an intermediate model. For example, a `User` model might have many `Permission` models through a `Role` model.

Here is an example of how to define a has many-through relationship in Tonka ORM:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    public function permissions()
    {
        return $this->hasManyThrough(Permission::class, Role::class);
    }
}
```

#### Database Schema

To implement this relationship in your database, you would typically have foreign keys in the `Role` and `Permission` tables that reference the `User` and `Role` tables, respectively:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE roles (
    id INT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE permissions (
    id INT PRIMARY KEY,
    role_id INT,
    title VARCHAR(255),
    content TEXT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

This schema ensures that each permission is linked to a specific role, and each role is linked to a specific user, enforcing the "has many through" relationship at the database level.

#### Accessing the Relationship

To access the relationship, you can use the defined relationship methods on your models. Here is an example of how to access the permissions of a user through the role model:

```php
$user = User::find(1);
$permissions = $user->permissions();
```

In this example, the `permissions` method on the `User` model will retrieve all the permissions associated with the user through the role.

### One-to-One (Polymorphic)

A one-to-one polymorphic relationship allows an entity to belong to more than one other entity on a single association. For example, a `Tag` model might belong to either a `Post` model or an `Video` model.

Here is an example of how to define a one-to-one polymorphic relationship in Tonka ORM:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Tag extends Model
{
    public function taggable()
    {
        return $this->morphTo();
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Post extends Model
{
    public function tag()
    {
        return $this->morphOne(Tag::class, 'taggable');
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Video extends Model
{
    public function tag()
    {
        return $this->morphOne(Tag::class, 'taggable');
    }
}
```

#### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE tags (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    taggable_id INT,
    taggable_type VARCHAR(255)
);
```

This schema ensures that each tag can be linked to either a post or a video, enforcing the one-to-one polymorphic relationship at the database level.

#### Accessing the Relationship

To access the relationship, you can use the defined relationship methods on your models. Here is an example of how to access the tag of a post or video:

```php
$post = Post::find(1);
$tag = $post->tag();

$video = Video::find(1);
$tag = $video->tag();
```

In this example, the `tag` method on the `Post` and `Video` models will retrieve the associated tag for the post or video.

### One-to-Many (Polymorphic)

A one-to-many polymorphic relationship allows a model to belong to more than one other model on a single association. This is useful when a model can be associated with more than one type of model, and multiple instances at a time.

Consider a `Comment` model that can belong to either a `Post` or a `Video` model. To define this relationship, you would use the `morphTo` and `morphMany` methods:

Here is an example of how to define a one-to-many polymorphic relationship in Tonka ORM:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Post extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Video extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

In this example, the `Comment` model can belong to either a `Post` or a `Video`. The `Post` and `Video` models can have many `Comment` instances.

#### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    content TEXT,
    commentable_id INT,
    commentable_type VARCHAR(255)
);
```

This schema ensures that each comment can be linked to either a post or a video, enforcing the one-to-many polymorphic relationship at the database level.

#### Accessing the Relationship

To access the relationship, you can use the defined relationship methods on your models. Here is an example of how to access the comments of a post or video:

```php
$post = Post::find(1);
$comments = $post->comments();

$video = Video::find(1);
$comments = $video->comments();
```

In this example, the `comments` method on the `Post` and `Video` models will retrieve the associated comments for the post or video.

### Many-to-Many (Polymorphic)

A many-to-many polymorphic relationship allows a model to belong to more than one type of model on a single association. For example, a `Tag` model might be associated with both `Post` and `Video` models.

Consider a `Tag` model that can belong to either a `Post` or a `Video` model. To define this relationship, you would use the `morphToMany` and `morphedByMany` methods:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Tag extends Model
{
    public function posts()
    {
        return $this->morphToMany(Post::class, 'taggable');
    }

    public function videos()
    {
        return $this->morphToMany(Video::class, 'taggable');
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Post extends Model
{
    public function tags()
    {
        return $this->morphByMany(Tag::class, 'taggable');
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Video extends Model
{
    public function tags()
    {
        return $this->morphByMany(Tag::class, 'taggable');
    }
}
```

#### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column, along with a pivot table:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE tags (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE taggables (
    tag_id INT,
    taggable_id INT,
    taggable_type VARCHAR(255),
    FOREIGN KEY (tag_id) REFERENCES tags(id),
    PRIMARY KEY (tag_id, taggable_id, taggable_type)
);
```

This schema ensures that each tag can be linked to either a post or a video, enforcing the many-to-many polymorphic relationship at the database level.

#### Accessing the Relationship
To access the relationship, you can use the defined relationship methods on your models. Here is an example of how to access the tags of a post or video:

```php
$post = Post::find(1);
$tags = $post->tags();

$video = Video::find(1);
$tags = $video->tags();
```

In this example, the `tags` method on the `Post` and `Video` models will retrieve the associated tags for the post or video.

### Custom Polymorphic Types

In some cases, you may need to define custom polymorphic types to handle more complex relationships. Tonka ORM allows you to customize the polymorphic type column to suit your application's needs.

Consider a scenario where a `Like` model can belong to either a `Post`, `Comment`, or `Photo` model. To define this relationship with custom polymorphic types, you would use the `morphTo` and `morphMany` methods:

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Like extends Model
{
    public function likeable()
    {
        return $this->morphTo();
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Post extends Model
{
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Comment extends Model
{
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
```

```php
<?php

namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Photo extends Model
{
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
```

In this example, the `Like` model can belong to a `Post`, `Comment`, or `Photo`. The `Post`, `Comment`, and `Photo` models can have many `Like` instances.

#### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    content TEXT,
    post_id INT,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE photos (
    id INT PRIMARY KEY,
    url VARCHAR(255)
);

CREATE TABLE likes (
    id INT PRIMARY KEY,
    likeable_id INT,
    likeable_type VARCHAR(255)
);
```

This schema ensures that each like can be linked to a post, comment, or photo, enforcing the custom polymorphic relationship at the database level.

### Querying Related Models

With Tonka ORM, you can query related models outside models class.

#### Model Joins

As we have seen joins in the [Query Builder](query_builder.md?id=joins) section, you can also use joins on models to query related models.

Here is an example on how to get all comments from a post:

```php
$comments = Post::where('id = ?', [1])
                ->joinLeft(Comment::class, 'post_id', 'id')
                ->fetch(Comment::class);
```

In this example, we query a post's comments by using a "left join" on the post model. 

#### The `fetch` Method on Joins

The `fetch` method in Tonka ORM allows you to retrieve records from the database as model objects. This method is useful when you plan to use model properties or methods on each record. The `fetch` method returns a [collection](collection.md), allowing you to easily manage the records.

Here is an example of how to use the `fetch` method:

```php
use App\Models\User;

$admin_users = User::where('is_admin = ?', [true])
                ->fetch()
                ->filter(fn(User $user) => $user->isActive());
```

You can pass a model class to the `fetch` method when using joins:

```php
$orders = User::where('status = ?', ['active'])
            ->joinLeft(Order::class)
            ->fetch(Order::class)
            ->filter(fn(Order $order) => $order->isShipped());
```

This query retrieves the orders of all active users. By default, if none is specified, the users will be returned.

### Table Alias

When working with joins, you may want to use table aliases to make your queries more readable and to avoid conflicts with column names. Tonka ORM allows you to specify table aliases in your join queries.

Here is an example of how to use table aliases in a join:

```php
use App\Models\User;
use App\Models\Order;

$orders = User::where('is_admin = ?', [true])
            ->joinLeft(Order::class, 'o.user_id', 'u.id')
            ->fetch(Order::class)
            ->filter(fn(Order $order) => $order->isShipped());
```

In this example the `u` alias is assigned to the `User` table, and the `o` alias is assigned to the `Order` table. Table alias is specified in the model class:

```php
<?php
namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $table = 'users u';
}
```

```php
<?php
namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Order extends Model
{
    protected $table = 'orders o';
}
```

You can also use table aliases in more complex queries involving multiple joins:

```php
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

$products = User::where('o.status = ?', ['shipped'])
            ->joinLeft(Order::class, 'u.id', 'user_id')
            ->joinLeft(Product::class, 'o.product_id', 'p.id')
            ->fetch(Product::class);
```

When dealing with model relationships, especially in complex queries involving multiple joins, it's essential to use table aliases to avoid conflicts with primary key column names. This ensures that your queries remain clear and unambiguous.

Here is an example of how to use table aliases on primary keys in a join query:

```php
use App\Models\User;
use App\Models\Order;

$user = User::find(1);
$orders = $user->orders();
```

Here is `users` and `orders` database scheman:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY
);

CREATE TABLE orders (
    id INT PRIMARY KEY
);
```

In this example, the `u` alias is assigned to the `User` table, and the `o` alias is assigned to the `Order` table. This helps to avoid conflicts with the `id` column, which is the primary key in both tables.

To define table aliases in your model classes, you can set the `$table` property with the alias:

```php
<?php
namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class User extends Model
{
    protected $table = 'users u';

    protected $primaryKey = 'u.id';
}
```

```php
<?php
namespace App\Models;

use Clicalmani\Database\Factory\Models\Model;

class Order extends Model
{
    protected $table = 'orders o';

    protect $primaryKey = 'o.id';
}
```

By using table aliases, you can ensure that your queries are clear and avoid conflicts with primary key column names, making it easier to work with complex model relationships.

## Model Attributes Validation

Tonka ORM provides a robust validation system to ensure that your model attributes meet specific criteria before they are saved to the database. This helps maintain data integrity and prevents invalid data from being persisted.

### Defining Validation Rules

To define validation rules for your model attributes, you can use the `Clicalmani\Database\Factory\Validate` class on each model entity property.

Here is an example of how to define validation rules in a model entity class:

```php
<?php

use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Validate;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'emailUNIQUE',
    key: 'email',
    unique: true
)]
class UserEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        nullable: false,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 191,
        nullable: false
    ), Validate('required|string|max:191')]
    public VarChar $given_name;
    
    #[Property(
        length: 191,
        nullable: false
    ), Validate('required|string|max:191')]
    public VarChar $family_name;

    #[Property(
        length: 200,
        nullable: false
    ), Validate('required|email|unique:users')]
    public VarChar $email;

    #[Property(
        length: 200,
        nullable: false
    ), Validate('required|password|min:8|confirm:1|hash:1')]
    public VarChar $password;
}
```

In this example, the `given_name` and `family_name` attributes are required, must be a string, and cannot exceed 191 characters. The `email` attribute is required, must be a valid email address, and must be unique in the `users` table. The `password` attribute is required, must be a password, and must be at least 8 characters long, needs a password confirm field and accept hashing.

### Available Validators

Tonka ORM provides a variety of built-in validators that you can use to define validation rules for your model entity attributes. These validators help ensure that your data meets specific criteria before it is saved to the database.

Here is a list of available validators arguments and their usage:

- `required`: Ensures that the attribute is present and not empty.
- `sometimes`: Ensures that the attribute is sometimes present and not empty.
- `nullable`: Ensures that the attribute may not be present and considered as `null`.
- `string`: Ensures that the attribute is a string.
- `string[]`: Ensures that the attribute is an array of strings.
- `bool`: Ensures that the attribute is a boolean.
- `int`: Ensures that the attribute is an integer.
- `float`: Ensures that the attribute is a float.
- `number`: Ensures that the attribute is a number.
- `numeric`: Ensures that the attribute is numeric.
- `numeric[]`: Ensures that the attribute is an array of numeric values.
- `email`: Ensures that the attribute is a valid email.
- `date`: Ensures that the attribute is a valid date.
- `datetime`: Ensures that the attribute is a date-time.
- `id`: Ensures that the attribute is a unique index.
- `file`: Ensures that the file is uploaded.
- `image`: Ensures that the uploaded file is an image.
- `pdf`: Ensures that the uploaded file is a PDF.
- `url`: Ensures that the attribute is a url.
- `in`: Ensures that the attribute is one of the specified values.
- `regex`: Ensures that the attribute matches the specified regular expression pattern.
- `json`: Ensures that the attribute is json decodable.

### `string` Validator

The `string` validator argument ensures that the attribute value is a string. You can combine it with specific options to further customize the validation rule.

Here is an example:

```php
#[Property(
    length: 255,
    nullable: false
), Validate('required|string|min:8|max:255')]
public VarChar $name;

#[Property(
    length: 10,
    nullable: false
), Validate('required|string|length:10')]
public Char $code;
```

In this example, the `string` validator argument ensures that the `name` attribute is a string, required, and has a minimum length of 8 characters and a maximum length of 255 characters. The `code` attribute is also a string, required, and must have an exact length of 10 characters.

### `string[]` Validator

The `string[]` validator argument ensures that the attribute value is an array of strings. You can combine it with specific options to further customize the validation rule.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|string[]|min:1|max:5')]
public array $tags;
```

In this example, the `tags` attribute is required, must be an array of strings, and each tag has a length between 1 and 5 characters.

### The `bool` Validator

The `bool` validator argument ensures that the attribute value is a boolean. This is useful when you need to validate that an attribute is either `true` or `false`.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|bool')]
public bool $is_active;
```

In this example, the `is_active` attribute is required and must be a boolean value (0, 1, `0`, `1` or `''`).

### `int` Validator

The `int` validator argument ensures that the attribute value is an integer. You can combine it with specific options to further customize the validation rule.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|int|min:1|max:100')]
public int $age;

#[Property(
    nullable: false
), Validate('required|int|range:1-100')]
public int $age;
```

In this example, the `age` attribute is required, must be an integer, and must be between 1 and 100.

### `float` Validator

The `float` validator argument ensures that the attribute value is a floating-point number. You can combine it with specific options to further customize the validation rule.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|float|min:0.1|max:99.9')]
public float $price;

#[Property(
    nullable: false
), Validate('required|float|range:0.1-99.9')]
public float $price;
```

In this example, the `price` attribute is required, must be a floating-point number, and must be between 0.1 and 99.9.

### `numeric` Validator

The `numeric` validator argument ensures that the attribute value is numeric. You can combine it with specific options to further customize the validation rule.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|numeric|min:1|max:100')]
public float $score;

#[Property(
    nullable: false
), Validate('required|numeric|length:3')]
public float $score;
```

In this example, the `score` attribute is required, must be numeric, and must be between 1 and 100.

### `numeric[]` Validator

The `numeric[]` validator argument ensures that the attribute value is an array of numeric values. You can combine it with specific options to further customize the validation rule.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|numeric[]|min:1|max:100')]
public array $scores;
```

In this example, the `scores` attribute is required, must be an array of numeric values, and each score must be between 1 and 100.

### `email` Validator

The `email` validator argument ensures that the attribute value is a valid email address. This is useful when you need to validate that an attribute contains a properly formatted email address.

Here is an example:

```php
#[Property(
    length: 255,
    nullable: false
), Validate('required|email|max:255')]
public VarChar $email;
```

In this example, the `email` attribute is required, must be a valid email address, and cannot exceed 255 characters in length.

### `date` Validator

The `date` validator argument ensures that the attribute value is a valid date. This is useful when you need to validate that an attribute contains a properly formatted date.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|date|format:Y-m-d')]
public Date $birthdate;
```

In this example, the `birhtdate` attribute is required, must be a valid date, and must follow the `Y-m-d` format.

!> The format option is compatible with PHP `date` function format.

### `datetime` Validator

The `datetime` validator argument ensures that the attribute value is a valid date-time. This is useful when you need to validate that an attribute contains a properly formatted date-time string.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|datetime|format:Y-m-d H:i:s')]
public DateTime $created_at;
```

In this example, the `created_at` attribute is required, must be a valid date-time, and must follow the `Y-m-d H:i:s` format.

### `id` Validator

The `id` validator argument ensures that the attribute value is a unique index. This is useful when you need to validate that an attribute contains a unique identifier.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|id|model:user')]
public Integer $user_id;
```

In this example, the `user_id` attribute is required and must be a unique identifier.

### `file` Validator

The `file` validator argument ensures that the attribute value is an uploaded file.

Here is an example:

```php
<?php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Validation\AsValidator;

class AttachmentController extends Controller
{
    /**
     * Store new document
     *
     * @return \Clicalmani\Foundation\Http\ResponseInterface
     */
    #[AsValidator(
        document: 'required|file|max:10469376'
    )]
    public function store(Request $request) : ResponseInterface
    {
        // ...
    }
}
```

In this example, the `document` attribute is required and must be an uploaded file with a maximum size of 10M.

### `image` Validator

The `image` validator argument ensures that the attribute value is an uploaded image file.

Here is an example:

```php
<?php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Validation\AsValidator;

class UserController extends Controller
{
    /**
     * Store new document
     *
     * @return \Clicalmani\Foundation\Http\ResponseInterface
     */
    #[AsValidator(
        profile_picture: 'required|image|max:2097152'
    )]
    public function store(Request $request) : ResponseInterface
    {
        // ...
    }
}
```

In this example, the `profile_picture` attribute is required and must be an uploaded image file with a maximum size of 2M.

### `pdf` Validator

The `pdf` validator argument ensures that the attribute value is an uploaded PDF file.

Here is an example:

```php
<?php
namespace App\Http\Controllers;

use Clicalmani\Foundation\Http\RequestController as Controller;
use Clicalmani\Foundation\Validation\AsValidator;

class AttachmentController extends Controller
{
    /**
     * Store new document
     *
     * @return \Clicalmani\Foundation\Http\ResponseInterface
     */
    #[AsValidator(
        document: 'required|pdf|max:2097152'
    )]
    public function store(Request $request) : ResponseInterface
    {
        // ...
    }
}
```

In this example, the `document` attribute is required and must be an uploaded PDF file.

### `url` Validator

The `url` validator argument ensures that the attribute value is a valid URL. This is useful when you need to validate that an attribute contains a properly formatted URL.

Here is an example:

```php
#[Property(
    length: 255,
    nullable: false
), Validate('required|url|max:255')]
public VarChar $website;
```

In this example, the `website` attribute is required, must be a valid URL, and cannot exceed 255 characters in length.

### `in` Validator

The `in` validator argument ensures that the attribute value is one of the specified values. This is useful when you need to validate that an attribute contains a value from a predefined set.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|in:admin,user,guest')]
public VarChar $role;
```

In this example, the `role` attribute is required and must be one of the values `admin`, `user`, or `guest`.

### `regex` Validator

The `regex` validator argument ensures that the attribute value matches the specified regular expression pattern. This is useful when you need to validate that an attribute contains a value that matches a specific pattern.

Here is an example:

```php
#[Property(
    length: 255,
    nullable: false
), Validate('required|regex:/^[a-zA-Z0-9_]+$/')]
public VarChar $username;
```

In this example, the `username` attribute is required and must match the regular expression pattern, allowing only alphanumeric characters and underscores.

### `json` Validator

The `json` validator argument ensures that the attribute value is JSON decodable. This is useful when you need to validate that an attribute contains a properly formatted JSON string.

Here is an example:

```php
#[Property(
    nullable: false
), Validate('required|json|decode:1')]
public Json $settings;
```

In this example, the `settings` attribute is required and must be a valid JSON string, the `decode` option is used to decode the json string before storing it.

### Validating Model Instances

When validators are used on a model entity, the instance will be automatically validated before saving to the database. This ensures that the data meets the defined criteria and helps maintain data integrity.

When you create a new instance of the model and attempt to save it, the validation rules will be automatically applied:

```php
use Clicalmani\Validation\Exceptions\ValidationException;

$user = new User;
$user->name = 'John Doe';
$user->email = 'john.doe@example.com';
$user->password = password('secret');

try {
    $user->save();
} catch (ValidationException $e) {
    // Handle the validation exception, e.g., return an error response
    echo 'Validation error: ' . $e->getMessage();
}
```

In this example, the `save` method will automatically validate the model's attributes against the defined validation rules. If any of the rules are violated, a `ValidationException` will be thrown, and you can handle the exception accordingly.

## Creating a Custom Validator

Tonka ORM allows you to create custom validators to handle specific validation logic that may not be covered by the built-in validators. This can be useful when you need to enforce custom validation rules for your model attributes, routes or controllers.

### Defining a Custom Validator

To define a custom validator, you can create a new class that extends the `Clicalmani\Validation\Validator` class and implements the `validate` method. This method should contain the custom validation logic.

Here is an example of how to create a custom validator:

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

In this example, the `PhoneNumberValidator` class extends the `Validator` class and implements the `validate` method to check if the value matches a specific phone number pattern. The `message` method returns a custom error message if the validation fails.

### Register the Custom Validator

To register the custom validator, you need to add it to the `validators` list in `app\Http\Kernel.php` file. This ensures that the custom validator is available for use when validating model attributes.

Here is an example of how to register the custom validator in a model entity:

```php
<?php

namespace App\Http;

use App\Validators\PhoneNumberValidator;

class Kernel
{
    protected $validator = [
        PhoneNumberValidator::class,
    ];
}
```

### Using the Custom Validator

To use the custom validator in your model entity, you can pass the validator rule to `Validate` class on the entity property.

Here is an example of how to use the custom validator in a model entity:

```php
<?php

namespace Database\Entites;

use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Validate;

class UserEntity extends Entity
{
    #[Property(
        length: 8,
        nullable: false
    ), Validate('required|phone|pattern:[0-9]{8}')]
    public Char $phone;
}
```

!> Notice that the custom validator can be used to validate routes, requests or controllers.