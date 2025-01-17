- [Introduction](query_builder?id=introduction)
- [Running Database Queries](query_builder?id=running-database-queries)
    - [Retrieving all rows from a table](query_builder?id=retrieving-all-rows-from-a-table)
    - [Retrieving a Single Row / Column From a Table](query_builder?id=retrieving-a-single-row-column-from-a-table)
    - [Retrieving a List of Column Values](query_builder?id=retrieving-a-list-of-column-values)
    - [Chunking Results](query_builder?id=chunking-results)
    - [Streaming Results Lazily](query_builder?id=streaming-results-lazily)
    - [Aggregates](query_builder?id=aggregates)
    - [Determining if Records Exist](query_builder?id=determining-if-records-exist)
    - [Select Statements](query_builder?id=select-statements)
        - [Specifying a Select Clause](query_builder?id=specifying-a-select-clause)
        - [Joins](query_builder?id=joins)
            - [Natural Join Clause](query_builder?id=natural-join-clause)
            - [Left Join / Right Join Clause](query_builder?id=left-join-right-join-clause)
            - [Inner Join Clause](query_builder?id=inner-join-clause)
            - [Cross Join Clause](query_builder?id=cross-join-clause)
            - [Advanced Join Clauses](query_builder?id=advanced-join-clauses)
                - [Custom Join Clause](query_builder?id=custom-join-clause)
                - [Sub Query Join Clause](query_builder?id=sub-query-join-clause)

## Introduction
Tonka ORM provides a convenient and smooth interface for creating and executing database queries. It can be used to perform most database operations in your application and works seamlessly with all [PDO](https://www.php.net/manual/fr/book.pdo.php) supported database systems.

The `Clicalmani\Database\DB` class uses the PHP Data Objects ([PDO](https://www.php.net/manual/fr/book.pdo.php)) extension interface to access the database. It creates a single [PDO](https://www.php.net/manual/fr/book.pdo.php) instance when connecting to the database and makes it available to all queries. In case you want to access the single instance and live the real experience with [PDO](https://www.php.net/manual/fr/book.pdo.php), Tonka ORM provides you with the static `getPDO()` method.

```php
$pdo = \Clicalmani\Database\DB::getPdo();
```

## Running Database Queries

### Retrieving all rows from a table

You can use the table method provided by the DB facade to run a query. The table method returns a fluent query builder instance for the given table, allowing you to chain more constraints on the query and then finally retrieve the query results using the get method:

```php
<?php
 
namespace App\Http\Controllers;
 
use Clicalmani\Database\DB;
use Clicalmani\Foundation\Resources\View;
 
class UserController extends Controller
{
    /**
     * Show a list of all of the application's users.
     */
    public function index(): View
    {
        $users = DB::table('users')->get();

        return view('user.index', ['users' => $users]);
    }
}
```

The get method returns an instance of Clicalmani\Foundation\Collection\Collection containing the results of the query where each result is an instance of the PHP stdClass object. You can access the value of each column by accessing the column as a property of the object:

```php
use Clicalmani\Database\DB;
 
$users = DB::table('users')->get();
 
foreach ($users as $user) {
    echo $user->name;
}
```

!> Tonka Collections provide a variety of extremely powerful methods for mapping and reducing data. For more information about Collections, see the [Collection documentation](collections).

### Retrieving a Single Row / Column From a Table

If you just need to retrieve a single row from a database table, you can use the `first()` method of the database facade. This method will return a single `stdClass` object:

```php
$user = DB::table('users')->where('name = :name', ['name' => 'John'])->first();
 
return $user->email;
```

If you want to retrieve a single row from a database table, but throw an exception if no matching row is found, you can use the `firstOrFail()` method:

```php
$user = DB::table('users')->where('name = :name', ['name' => 'John'])->firstOrFail();
```

If you don't need an entire row, you can extract a single value from a record using the value method. This method will return the column value directly:

```php
$email = DB::table('users')->where('name = :name', ['name' => 'John'])->value('email');
```

To retrieve a single row by its ID column value, use the find method:

```php
$user = DB::table('users')->find(3);
```

### Retrieving a List of Column Values

If you want to retrieve an Clicalmani\Foundation\Collection\Collection instance containing the values ​​of a single column, you can use the pluck method. In this example, we will retrieve a collection of user titles:

```php
use Clicalmani\Database\DB;
 
$titles = DB::table('users')->pluck('title');
 
foreach ($titles as $title) {
    echo $title->value;
}
```

You may specify the column that the resulting collection should use as its keys by providing a second argument to the `pluck()` method:

```php
$titles = DB::table('users')->pluck('title', 'name');
 
foreach ($titles as $name => $title) {
    echo "$title->key:$title->value";
}
```

### Chunking Results

If you need to work with thousands of database records, consider using the chunk method provided by the DB facade. This method retrieves a small chunk of results at a time and feeds each chunk into a closure for processing. For example, let's retrieve the entire users table in chunks of 100 records at a time:

```php
use Clicalmani\Foundation\Collection\Collection;
use Clicalmani\Database\DB;
 
DB::table('users')->orderBy('id')->chunk(100, function (Collection $users) {
    foreach ($users as $user) {
        // ...
    }
});
```

You may stop further chunks from being processed by returning false from the closure:

```php
DB::table('users')->orderBy('id')->chunk(100, function (Collection $users) {
    // Process the records...
 
    return false;
});
```

If you are updating database records while chunking results, your chunk results could change in unexpected ways. If you plan to update the retrieved records while chunking, it is always best to use the chunkById method instead. This method will automatically paginate the results based on the record's primary key:

```php
DB::table('users')->where('active', false)
    ->chunkById(100, function (Collection $users) {
        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user['id'])
                ->update(['active' => true]);
        }
    });
```

!> When updating or deleting records inside the chunk callback, any changes to the primary key or foreign keys could affect the chunk query. This could potentially result in records not being included in the chunked results.

### Streaming Results Lazily

The lazy method works similarly to the `chunk` method in the sense that it executes the query in chunks. However, instead of passing each chunk into a callback, the `lazy()` method returns a LazyCollection, which lets you interact with the results as a single stream:

```php
use Clicalmani\Database\DB;
 
DB::table('users')->orderBy('id')->lazy()->each(function (object $user) {
    // ...
});
```
Once again, if you plan to update the retrieved records while iterating over them, it is best to use the `lazyBy` or `lazyByDesc` methods instead. These methods will automatically paginate the results based on the specified field:

```php
DB::table('users')->where('active = 1')
    ->lazyBy('id')->each(function (object $entry) {
        DB::table('users')
            ->where('id', $entry->value)
            ->update(['active' => true]);
    });
```

!> When updating or deleting records while iterating over them, any changes to the primary key or foreign keys could affect the chunk query. This could potentially result in records not being included in the results.

### Aggregates

The query builder also provides various methods to retrieve aggregate values ​​such as count, maximum, minimum, average, and sum. You can call any of these methods after you build your query:

```php
use Illuminate\Support\Facades\DB;
 
$users = DB::table('users')->count();
 
$price = DB::table('orders')->max('price');
```

Of course, you can combine these methods with other clauses to refine how your overall value is calculated:

```php
$price = DB::table('orders')
                ->where('finalized = 1')
                ->avg('price');
```

### Determining if Records Exist

Instead of using the count method to determine if there are any records that match your query constraints, you can use the `exists` and `doesntExist` methods:

```php
if (DB::table('orders')->where('finalized = 1')->exists()) {
    // ...
}
 
if (DB::table('orders')->where('finalized = 1')->doesntExist()) {
    // ...
}
```

### Select Statements

#### Specifying a Select Clause

It is not always desirable to select all columns from a database table. Using the select method, you can specify a custom "select" clause for the query:

```php
$users = DB::table('users')
            ->select('name, email as user_email');
```

!> The select parameter will be injected into the query as string, so you should be extremely careful to avoid creating SQL injection vulnerabilities.

The distinct method allows you to force the query to return distinct results:

```php
$users = DB::table('users')->distinct()->select();
```

### Joins

#### Natural Join Clause

The Query Builder can also be used to add join clauses to your queries. To perform a basic "natural join," you can use the `join` method on an instance of the `Query Builder`. The first argument passed to the `join` method is the name of the table you need to join to, while the last argument is an optional callback function. You can even join multiple tables in a single query:

```php
$users = DB::table('users u')
            ->join('contacts')
            ->join('orders')
            ->select('u.*, c.phone, o.price')
            ->get();
```

#### Left Join / Right Join Clause

If you would like to perform a "left join" or a "right join" instead of a "natural join", use the `joinLeft` and `joinRight` methods:

```php
$users = DB::table('users u')
            ->joinLeft('posts p', 'u.id', 'p.user_id')
            ->get();
 
$users = DB::table('users u')
            ->joinRight('posts p', 'u.id', 'p.user_id')
            ->get();
```

#### Inner Join Clause

You may use the `joinInner` method to perform an "inner join". The INNER JOIN selects records that have matching values in both tables:

```php
$users = DB::table('users u')
            ->joinInner('posts p', 'u.id', 'p.user_id')
            ->get();
```


#### Cross Join Clause

You may use the `joinCross` method to perform a "cross join". Cross joins generate a Cartesian product between the first table and the joined table:

```php
$sizes = DB::table('sizes')
            ->joinCross('colors')
            ->get();
```

#### Advanced Join Clauses

You may also specify more advanced join clauses. To get started, pass a closure as the second argument to `join` method. The closure will receive a `Clicalmani\Database\joinClause` instance which allows you to specify constraints on the "join" clause:

##### Custom Join Clause

You may pass a closure function as second argument to the `join` method to perform a custom join:

```php
$sizes = DB::table('users u')
            ->join('posts p', function(JoinClause $join) {
                $join->left()->on('u.id = p.user_id');
            })->get();
```

##### Sub Query Join Clause

You may specify a sub-query as a join clause by passing a closure function as second argument to the `join` method:

```php
$paid_credits = DB::table('credits c')
                    ->joinLeft('payments p', 'p.user_id', 'c.user_id')
                    ->join(function(JoinClause $join) {
                        $join->sub('SELECT * FROM users')
                             ->using('user_id')
                             ->as('up')
                             ->type('left');
                    })
                    ->groupBy('up.user_id')
                    ->get('SUM(p.amount) amount, up.firstname, up.lastname');
```









