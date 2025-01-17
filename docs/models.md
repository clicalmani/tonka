**Models**

Models can be considered as layers that must be manipulated to act indirectly on the underlying database table. We have two (2) types of tables in any database (as far as I know): primary tables and associative tables. Primary tables declare primary keys and associative tables refer to them as foreign keys. A primary key can be an association of one or more attributes. In this case, in your model, a primary key may be represented by a character string when it is a unique key and a non-associative array when it is a composit key.

It is easier to create a model from the `make:model` console command as follows:

```bash
php tonka make:model <model_name> <table_name> <primary_keys>
```

The model name, table name, and primary key(s) must be entered together. When your primary key is a composit key, separate each key with a space.

!> The model name must be derived from the entity name it represent by removing the Entity suffix. For example, the `UserEntity` entity will have the `User` model as its wrapper.

Let's create a model named `User` by providing the following command:

```bash
php tonka make:model User users user_id
```

Once the command is executed, a file named `User.php` will be created into `app/Models` directory and containing an initial code as follow:

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

    /**
     * Constructor 
     *
     * @param mixed $id
     */
    public function __construct(mixed $id = null)
    {
        parent::__construct($id);
    }
}
```
!> You will notice the presence of `$entity` property which is added automatically. That means if `UserEntity` can not been found in `database/entities` directory, the command will fails.

Most SQL langage support table and attribute aliases. Because it will be more convenient to name your table with alias when using join statements. As an attribute can bear the same name in more than one table. In this case you can add an alias to your table by separating it with a space with the table name.

!> Tonka ORM does not support the use of the operator `AS` as alias creator.

```php
/**
 * Model database table 
 *
 * @var string $table Table name
 */
protected $table = "users u";

/**
 * Table primary key(s)
 * Use an array if the key is composed with more than one attributes.
 *
 * @var string|array $primary_keys Table primary key.
 */
protected $primaryKey = "u.user_id";
```

### Model class methods

The Model class has several methods for creating and manipulating data.

#### find()

The `find()` method takes a single parameter which is the primary key and returns the record corresponding to that key. It accepts a string when dealing with a single key and a non-association array when dealing with a composit key. `NULL` will be returned when no record is associated to the specified key.

```php
<?php
$post = Post::find(1);
print $post->post_title; // Get post title
$post->publish_date = now(); // Set publish date
?>
```

!> Technically speaking, in the absence of a record, `find()` returns an empty record which will be considered as `NULL`.

#### where()

The `where()` method allows you to specify the _where condition_ of your SQL statement. It accepts two (2) parameters, the second being optional. The first parameter is the statement parameters and the second parameter specifies the parameters options. As you can see, this is the syntax of the `prepare()` method of [PDO](https://www.php.net/manual/fr/book.pdo.php). *Tonka* aims to respect the basic syntax of PHP, thus allowing anyone who has mastered the basics of the language to take advantage of the features that *Tonka* makes available without the need for a special academy.

Therefore all the SQL statements will not be prepared. *Tonka ORM* always verify the presence of the parameters options to decide whether to execute a prepared or a non-prepared statement.

```php
<?php
$result = Post::where('title LIKE "%my title%"')->select(); // Non-prepared statement
$result = Post::where('title LIKE :title', ['title' => '%my title%'])->select(); // Prepared statement
?>
```
The advantage of a prepared statement is that the codes injected (by a [SQL injection](https://en.wikipedia.org/wiki/SQL_injection) method) will have no effect. Because the entered data is processed after the execution of the statement.

Let's assume the title parameter used in the _where condition_ is being passed through a user form. Its value can be tricked to make the SQL statement vulnerable. That is why you must prevail the use of prepared statements over non-prepared statements.

#### whereAnd()

Acts like `where()` with the only difference being that it is an object method while `where()` is a class method. The main purpose was to allow conditions chaining.

The following query can be written using _two where conditions_.

```php
Post::where('LOCATE("sport", title) AND publish_date > :date', ['date' => now()->subDays(10)])->select();
```

```php
Post::where('LOCATE("sport", title)')->whereAnd('publish_date > :date', ['date' => now()->subDays(10)])->select();
```

#### whereOr()

Acts like `whereAnd()` but this time with `OR` as operator.

!> The thruth is `where()` method accepts 3 parameters. The second parameter is the statement operator being used to join the statement condition to the previous one. To allow the use of multiple conditions, the `whereAnd()` and `whereOr()` methods have been added; since `where()` is a class method.

#### get()

The `get()` method is the equivalent of `select()` method that we saw in the previous exemple. It accepts a single parameter and produces the query result as a [collection](collection.md). That is to say, it just returns the result produced by the database without any special processing.

```php
Post::where('user_id = :id', ['id' => 1])->get('post_title, post_content, publish_date'); // Collection of user's posts.
```

#### fetch()

Unlike `get()` method, `fetch()` processes the result of the SQL statement by casting each record to its corresponding model's instance. That is to say, it acts in the same way as if the key part of each record were passed directly to `find()` method. `fetch()` accepts a single parameter which is a model class, to cast the result set into instances of that class. We will see more case use of `fetch()` in [Fetching data in join statements](database/models.md#fetching-data-in-join-statements) section.

```php
Post::where('publish_date > :date', ['date', now()->subDays(10)])->fetch();
```

The above query will return a [collection](collection.md) of posts which meets the condition of the SQL statement.

#### insert()

The `insert()` method create new records into the database. It mimics the SQL INSERT command, several records ​​can be inserted at once.

```php
$movie = new Movie;
$movie->insert([
    ['title' => 'Le mépris', 'author' => 'Jean-Luc Godard', 'release_year' => '1963'],
    ['title' => 'Boulevard du crépuscule', 'author' => 'Billy Wilder', 'release_year' => '1950'],
    ['title' => 'La vie des autres', 'author' => 'Florian Hanckel', 'release_year' => '2006']
]);
```

#### create()

Same as `insert()`.

#### createOrFail()

Same as `create()` with the only difference that the SQL statement will be executed in a transaction. The method returns `true` on success and `false` on failure.

#### update()

`update()` updates data in the underlying table.

```php
Movie::where('movie_id = :id', ['id' => 1])->update([
    'movie_title' => 'La nuit de noce chez les fantômes',
    'publish_date' => '1985-02-23'
]);
```
#### save()

It allows you to save modifications to the database. Whether it is an `insert()` or an `update()`, table attributs values can be set on a class object. Then a call to `save()` method would be necessary to save changes.

```php
# Insert new post
$movie = new Movie;
$movie->movie_title = 'La nuit de noce chez les fantômes';
$movie->release_year = '2003';
$post->publish_date = '2003-05-10';
$post->save(); // Saves modifications to the database

# Update an existing post
$movie = Movie::find(1);
$movie->movie_title = 'La nuit de noce chez les fantômes';
$movie->release_year = '2003';
$post->publish_date = '2003-05-10';
$post->save();
```
#### swap()
It retrieves the request parameters and treat them as the underlying table attributes. However, the parameters will be sanitize to eliminate any uncertainty before being checked in the table. Ultimately, the swap() method is the safest way to insert or modify table data from query parameters. This would reduce a few keyboard strokes altogether.

Let's take an exemple of a user registration form submission.

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>User Registration Form</h1>
    <form action="/register" method="post">
        <label for="given_name">Given name:</label>
        <p><input type="text" name="given_name" placeholder="Given name"/>
        <label for="family_name">Family name:</label>
        <p><input type="text" name="family_name" placeholder="Family name"/>
        <label for="email">Email:</label>
        <p><input type="email" name="email" placeholder="Email address"/>
        <label for="phone">Phone:</label>
        <p><input type="tel" name="phone" placeholder="Phone number"/>
        <p><input type="submit" value="Create User"/></p>
    </form>
</body>
</html>
```

```php
<?php
# UserController.php

public function store(Post $post)
{
    $post->swap();
    $post->save();
}
?>
```

The call to the `swap()` method will retrieve the form parameters and create a new record in the underlying post table with those parameters as table attributs. Therefore we are not required to use the exact attributs names as form fields. To do this we must use `swap()` in conjonction with a custom [request](request.md) as the second argument of the store method.

```php
<?php
# UserController.php

public function store(Post $post, StoreUserRequest $request)
{
    $post->swap();
    $post->save();
}
?>
```

#### filter()

The `filter()` method works in the same manner as `swap()` method by reading request parameters. But this time, parameters are used to filter the query result. Let's say we want to retrieve posts published by a given user by making a request on a [route](routing/routes.md) like `/post?user_id=1` which use `PostController::index()` method as its action:

```php
<?php
// PostController.php

public function index()
{
    return response()->json(
        Post::filter()->toArray()
    );
}
?>
```
The call to `filter()` method will retrieve the `user_id` parameter and make a SQL statement using this user as its condition. Therefore parameters will be sanitize and check against post table attributs.

The `filter()` method accepts two (2) optional parameters. The first parameter is an array that indicates the list of query parameters to exclude. The second parameter is a table which contains options for the SQL statement which will be generated following this query:

```
- order_by allows you to order the result of the SQL command
- limit allows you to limit the size of the result
- offset indicates the index where to start reading
```

```php
<?php
// PostController.php

public function index()
{
    return response()->json(
        Post::filter(
            ['attachment'], // Exclude attachment
            [
                'order_by' => 'publish_date DESC',
                'limit' => 10,
                'offset' => 0
            ]
        )->toArray()
    );
}
?>
```

!> As is the case for the `swap()` method, the parameters can be subprocessed by a custom [request](request.md) before being passed to the `filter()` method.

#### all()

It allows you to retrieve all table records.

```php
<?php
// PostController.php

public function index()
{
    return response()->json(
        Post::all()->toArray()
    );
}
?>
```

#### delete()

If called on an instance, the record whose key matches that instance will be permanently deleted from the table.

```php
<?php
// PostController.php

public function destroy()
{
    Post::where('user_id = :id', ['id' => 1])
        ->fetch()
        ->each(fn(Post $post) => $post->delete());
}
```

#### forceDelete()

Sometimes we would want to delete a certain number of records at a time respecting a given condition. This is where the `forceDelete()` method comes in action.

```php
<?php
// PostController.php

public function destroy()
{
    Post::where('publish_date > :date', ['date' => '2003-10-23'])
        ->forceDelete();
}
```

After running this command, the table records matched in the where condition will deleted permanently.

> [!NOTE]
> records

#### softDelete()

Its main purpose is to test a deletion. Just after the operation the deleted occurrence is restored. This is a way to delete without following it with commit() in a transaction.

```php
<?php
// PostController.php

public function destroy()
{
    Post::where('user_id = :id', ['id' => 1])
        ->fetch()
        ->each(fn(Post $post) => $post->softDelete());
}
```

#### first()

As you can see, when the execution of an SQL command must produce several results, these will be made into a collection (we will discuss collections later). The first() method therefore the first value of the collection.

```php
Post::where('user_id = :id', ['id' => 1])
    ->orderBy('publish_date DESC')
    ->first();
```

#### distinct()

It accepts a parameter that allows you to enable or disable the distinct selection of occurrences in the SQL result.

```php
User::where()
    ->distinct()
    ->select('first_name');
```

#### ignore()

It allows you to ignore the signaling of duplicates when inserting or modifying data.

```php
<?php
// UserController.php

public function store(User $user)
{
    $user->swap();
    $user->ignore();
    $user->save();
}
?>
```

#### lastInsertId()

It allows you to retrieve the value of the generated key when it is an auto-incremented key. This method also goes beyond trying to guess the value of the key even if it is not a generate key.

```php
<?php
// UserController.php

public function store(User $user)
{
    $user->swap();
    $user->save();

    return response()->success([
        'id' => $user->lastInsertId()
    ])
}
?>
```

### Events

#### The boot method
Boot method listens to events automatically emitted at 3 levels when the model state changes: `create`, `update` and `delete`. Each state triggers 2 events (`before` event and `after` event). So you will have a total of 6 events that can be listen in `boot()` method.

- `beforeCreate` event is emitted before storing data in the underlying table,
- `afterCreate` event is emitted after data are stored in the underlying table,
- `beforeUpdate` event is emitted before updating data in the underlying table,
- `afterUpdate` event is emitted after data is beeing updated in the underlying table,
- `beforeDelete` event is emitted before deleting data in the underlying table,
- `afterDelete` event is emitted after data are being deleted from the underlying table

Let's take an exemple of deleting the user profile photo before deleting the user.

```php
// User.php

protected function boot() : void
{
    $this->beforeDelete(function(User $user) {
        if (file_exists($user->photo)) {
            unlink($user->photo);
        }
    });
}
```

#### Custom events
A model can emit custom events directly using the built-in `emit()` method. The `emit()` method accepts 2 parameters, the first parameter is the event name and the second parameter is data to pass to the event handler.

Events must be initialized in `EventServiceProvider.php` located in `app/Providers` directory.

```php
<?php
namespace App\Providers;

use Clicalmani\Fundation\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register custom events
     * 
     * @return void
     */
    public function register() : void
    {
        // Here we create a product:purchased event
        $this->createEvent('product:purchased');
    }

    /**
     * Add listeners
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->addListener('product:purchased', '<Event listener>');
    }
}
```

A listener is the second parameter of `App\Providers\EventServiceProvider::addListener()` method. It can be a class with a handler() method or an anonymous function.

To quickly create an event listener class we must run `make:event` command as follow:

```bash
php tonka make:event ProductPurchaseListener
```

After running this command `ProductPurchaseListener.php` will be created in `app/Events` directory with an initial code:

```php
<?php
namespace App\Events;

use Clicalmani\Database\Factory\Models\Events\Event;

class AssignTeacherEventListener
{
    /**
     * Event handler
     * 
     * @param \Clicalmani\Database\Factory\Models\Events\Event $event
     * @param mixed $data Event data
     * @return void
     */
    public function handler(Event $event, mixed $data) : void
    {
        /**
         * TODO
         */
    }
}
```

!> `Clicalmani\Database\Factory\Models\Events\Event` is just a class with two (2) public properties (`$name` and `$target`). The `$name` property holds the event name and the `$target` property holds the event target which is the model instance that emit the event.

Now we can specify our listener in `addListener()` method.

```php
<?php
// EventServiceProvider.php

/**
 * Add listeners
 * 
 * @return void
 */
public function boot(): void
{
    $this->addListener('product:purchased', ProductPurchasedListener::class);
}
```

After then we can emit `product:purchased` event anywhere, the handler method of our listener will be called.

## Relations

Tonka ORM has 3 methods to establish relationships between models.

### hasOne()

This is the relationship of foreign keys. It gives access to the parent table model through the foreign key.

```php
<?php
// PostController.php

public function user()
{
    return $this->hasOne(User::class, 'user_id', 'user_id');
}

// ...

$post = Post::find(1);
$user = $post->user(); // Will return User
?>
```

!> `hasOne()` method of `Clicalmani\Database\Factory\Models\Model` accept 3 parameters. The second and third parameters are `foreign_key` et `parent_key` respectively. `foreign_key` is the key name in the child entity and `parent_key` is the key name in parent entity. If foreign key bears the same name in the parent entity, then the parent key parameter can be omitted.

### belongsTo()

When we establish the hasOne relationship in reverse we obtain a belongsTo(). In our precedent exemple we can use `belongsTo` instead of `hasOne` by reading the key back from `user` table.

```php
<?php
// PostController.php

public function user()
{
    return $this->belongsTo(Post::class, 'user_id');
}

// ...

$post = Post::find(1);
$user = $post->user(); // Will return User
?>
```

### hasMany()

Allows you to establish a one-multiple relationship between two tables. Let’s take the example of the user-post relationship. A user having published several articles and an article having been published by one and only one user.

```php
<?php
// UserController.php

public function posts()
{
    return $this->hasMany(Post::class, 'user_id');
}

// ...

$user = Post::find(1);
$posts = $user->posts(); // Collection of user posts
?>
```
## Joins

### Inner Join
As you have seen later in the [Query Builder](database/db.md) section, a model may also be used to add join clauses to your queries. To perform a basic "inner join", you may use the join method on any model instance. The first argument passed to the join method is the class name of the model to join to, the second argument is the foreign key representation in the joined table and the third argument (which is optional) is the parent key representation in the table to join to. You may even join multiple tables in a single query:

```php
use App\Models\Product;
use App\Models\Order;
use App\Models\User;

Product::where('user_id = :user AND product_id = :product', ['user_id' => 1, 'product_id' => 3])
    ->innerJoin(Order::class, 'product_id', 'product_id') // We can simply ignore the third argument as 
                                                          // the key has the same representation in both tables
    ->innerJoin(User::class, 'user_id')
    ->get('o.*'); // Let's asume o is define as order table alias in Order class
```

### Left/Right Join
If you would like to perform a "left join" or "right join" instead of an "inner join", use the `leftJoin()` or `rightJoin()` methods. These methods have the same signature as the `innerJoin()` method:

```php
use App\Models\User;
use App\Models\Post;

User::where('user_id = :id', ['id' => 2])
    ->leftJoin(Post::class, 'user_id')
    ->get();

User::where('user_id = :id', ['id' => 2])
    ->rightJoin(Post::class, 'user_id')
    ->get();
```

!> Instead of calling the method `leftJoin()`, you may also call the method `join()` with the same arguments to perform a `Left Join'.

### Cross Join
You may use the `crossJoin()` method to perform a "cross join". Cross joins generate a cartesian product between the first table and the joined table:

```php
Veg::where()
    ->crossJoin(Fruit::class)
    ->get();
```

### Sub queries
You may use the `subQuery()` method to join a query to a subquery. The `subQuery()` method receives two arguments: a closure that defines the relation and join flags. In this exemple we will retrieve the total amount of the disbursement made by each creditors and the remaining amount to pay between two dates:

```sql
SELECT (c.amount - sub.paid_amount) due_amount, u.user_id FROM credits c
    LEFT JOIN users u ON(c.user_id=u.user_id)
    LEFT JOIN (SELECT SUM(amount) paid_amount, user_id FROM disburse WHERE disburse_date BETWEEN '2003-05-20' AND DATE(NOW())) AS sub 
        ON(u.user_id=sub.user_id)
    HAVING due_amount > 0
    GROUP BY c.amount;
```

```php
Credit::where()
    ->leftJoin(User::class, 'user_id')
    ->subQuery(function() {
        return Disburse::where('disburse_date BETWEEN :start_date AND :end_date', ['start_date' => "2003-05-20", 'end_date' => now()->format('Y-m-d')]);
    }, [
        'join' => 'LEFT',
        'subfields' => 'SUM(amount) paid_amount, user_id',
        'alias' => 'sub',
        'condition' => 'ON(u.user_id=sub.user_id)'
    ])
    ->having('due_amount > 0')
    ->groupBy('c.amount')
    ->get();
```

### Fetching data in a join query
Sometime you may want to retrieve the result of a single table in a join query. The `fetch()` method allows you to retrieve the result of a table by specify the model class of the underlying table:

```php
use App\Models\User;
use App\Models\Post;

User::where('user_id = :id', ['id' => 2])
    ->leftJoin(Post::class, 'user_id')
    ->fetch();
```
In the above exemple, `fetch()` will retrieve the query result from users table as it's the first table of the juncture. If you want it to fetch from posts table you may specify `Post::class` as its only argument.

```php
use App\Models\User;
use App\Models\Post;

User::where('user_id = :id', ['id' => 2])
    ->leftJoin(Post::class, 'user_id')
    ->fetch(Post::class);
```