# Getting Started

- [Introduction](orm?id=introduction)

## Introduction

Tonka ORM is a powerful and flexible Object-Relational Mapping tool designed to simplify database interactions in your applications. By abstracting the complexities of SQL queries, Tonka ORM allows developers to work with database records as if they were regular objects in their programming language of choice. This not only speeds up development time but also ensures that your codebase remains clean and maintainable. Whether you are building a small project or a large-scale enterprise application, Tonka ORM provides the tools and features necessary to manage your data efficiently and effectively.

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

This command will generate a new file named `Product.php` (or similar, depending on your project's configuration) with a basic structure for your model class. You can then customize this file by adding fields and methods as needed.

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

$flights = Flight::where('active = 1')
               ->orderBy('name')
               ->top(10)
               ->get();
```

### Collections

As we have seen, methods like `all` and `get` retrieve multiple records from the database. However, these methods don't return a plain PHP array. Instead, an instance of `Clicalmani\Foundation\Collection\Collection` is returned.

```php
$flights = Flight::where('destination', 'Paris')->get();
 
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

### Available Query Builder Methods

- `all()`: Retrieve all records from the table.
- `find($id)`: Retrieve a record by its primary key (if the primary key is a composit key, use an array insted).
- `where($column, $operator, $value)`: Add a basic where clause to the query.
- `orderBy($column, $direction)`: Add an order by clause to the query.
- `limit($value)`: Limit the number of records returned.
- `get(?string $fields = '*') : Collection`: Execute the query and retrieve the results.
- `select(?string $fields = '*') : Collection`: Same as get.
- `fetch(?string $class = null) : Collection`: Fetch the result set.
- `function delete() : bool`: Delete the model.
- `forceDelete() : bool`: When use in combination with `where()` all the matched rows will be deleted.
- `softDelete() : bool`: Similate a record delete.
- `update(?array $values = []) : bool`: Updates the model record.
- `insert(array $fields = [], ?bool $replace = false) : bool`: You can instanciate an empty model and then invoke the insert method to create a record in the table. The `$replace` parameter is useful when you want the record to be updated if already exists.
- `create(array $fields = [], ?bool $replace = false) : bool`: Same as `insert()`.

These methods can be chained together to build more complex queries. By using the query builder, you can easily interact with your database without writing raw SQL, making your code more readable and maintainable.
