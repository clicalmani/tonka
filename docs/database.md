- [Introduction](database.md?id=introduction)
- [Supported Databases](database.md?id=supported-databases)
- [Configuration](database.md?id=configuration)
    - [SQLite Configuration ](database.md?id=sqlite-configuration )
    - [Microsoft SQL Server Configuration](database.md?id=microsoft-sql-server-configuration)
    - [Configuration Using URLs](database.md?id=configuration-using-urls)
- [Running SQL Queries](database.md?id=running-sql-queries)
    - [Running a Select Query](database.md?id=running-a-select-query)
        - [Using the Query Builder](database.md?id=using-the-query-builder)
        - [Using Raw SQL](database.md?id=using-raw-sql)
        - [Fetching a Single Row](database.md?id=fetching-a-single-row)
        - [Using Named Bindings](database.md?id=using-named-bindings)
            - [Using Named Bindings with the Query Builder](database.md?id=using-named-bindings-with-the-query-builder)
            - [Using Named Bindings with Raw SQL](database.md?id=using-named-bindings-with-raw-sql)
    - [Running a General Statement](database.md?id=running-a-general-statement)
    - [Running an Unprepared Statement](database.md?id=running-an-unprepared-statement)
- [Implicit Commits](database.md?id=implicit-commits)
    - [Statements That Cause Implicit Commits](database.md?id=statements-that-cause-implicit-commits)
    - [Example of Implicit Commit](database.md?id=example-of-implicit-commit)
- [Managing Transactions](database.md?id=managing-transactions)
- [Using Multiple Database Connections](database.md?id=using-multiple-database-connections)
    - [Configuring Multiple Connections](database.md?id=configuring-multiple-connections)
    - [Using Multiple Connections](database.md?id=using-multiple-connections)
        - [With the Query Builder](database.md?id=with-the-query-builder)
        - [Using **Tonka** Database ORM](database.md?id=using-tonka-database-orm)
    - [Switching Connections at Runtime](database.md?id=switching-connections-at-runtime)
- [Listening for Query Events](database.md?id=listening-for-query-events)
    - [Registering a Query Listener](database.md?id=registering-a-query-listener)
    - [Example Usage](database.md?id=example-usage)
- [Monitoring Cumulative Query Time](database.md?id=monitoring-cumulative-query-time)
    - [Registering a Cumulative Query Time Listener](database.md?id=registering-a-cumulative-query-time-listener)
    - [Example Usage Of Cumulative Query Time Listener](database.md?id=example-usage-of-cumulative-query-time-listener)
- [Database Transactions](database.md?id=database-transactions)
    - [Using Transactions](database.md?id=using-transactions)
    - [Using the `transaction` Method](database.md?id=using-the-transaction-method)
- [Handling Deadlocks](database.md?id=handling-deadlocks)
- [Connecting to the Database CLI](database.md?id=connecting-to-the-database-cli)
    - [Installing the CLI](database.md?id=installing-the-cli)
    - [Using the CLI](database.md?id=using-the-cli)
        - [Connecting to the Database](database.md?id=connecting-to-the-database)
        - [Running SQL Queries in the CLI](database.md?id=running-sql-queries-in-the-cli)
        - [Managing Migrations](database.md?id=managing-migrations)
        - [CLI Help](database.md?id=cli-help)
    - [Inspecting Your Databases](database.md?id=inspecting-your-databases)
    - [Viewing Table Structure](database.md?id=viewing-table-structure)
    - [Analyzing Query Performance](database.md?id=analyzing-query-performance)
    - [Monitoring Database Activity](database.md?id=monitoring-database-activity)
    - [Viewing Database Logs](database.md?id=viewing-database-logs)

## Introduction

**Tonka** Database is a powerful and scalable database solution designed to handle large volumes of data with ease. It offers robust features for data management, high availability, and security, making it an ideal choice for enterprise applications. With its user-friendly interface and comprehensive documentation, **Tonka** Database simplifies the process of database administration and development.

Almost every modern web application interacts with a database. **Tonka** makes interacting with databases extremely simple across a variety of supported databases using raw SQL, a fluent query builder, and the **Elegant ORM**. Currently, **Tonka** provides first-party support for five databases:

## Supported Databases

1. **MySQL**: A widely used open-source relational database management system.
2. **PostgreSQL**: An advanced, enterprise-class open-source relational database.
3. **SQLite**: A self-contained, high-reliability, embedded, full-featured, public-domain, SQL database engine.
4. **SQL Server**: A relational database management system developed by Microsoft.
5. **Oracle**: A multi-model database management system produced and marketed by Oracle Corporation.

Each of these databases is fully supported and can be easily integrated with **Tonka** Database, providing flexibility and scalability for your applications.
## Configuration

To configure **Tonka** Database for your application, follow these steps:

1. **Install the Database Driver**: Ensure that the appropriate database driver for your chosen database is installed. This can typically be done via your package manager.

2. **Environment Variables**: Use environment variables to manage your database credentials securely. Create a `.env` file in the root of your project and add the necessary variables:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

### SQLite Configuration 

If you are using SQLite, the configuration is slightly different. You need to specify the path to your SQLite database file in the `.env` file:
        ```env
        DB_CONNECTION=sqlite
        DB_DATABASE=/absolute/path/to/your_database.sqlite
        ```

        Alternatively, you can use a relative path:
        ```env
        DB_CONNECTION=sqlite
        DB_DATABASE=database/database.sqlite
        ```

        Ensure that the directory containing your SQLite database file is writable by your application.

By default, foreign key constraints are enabled for SQLite connections. If you would like to disable them, you should set the `DB_FOREIGN_KEYS` environment variable to `false` in your `.env` file:

```env
DB_FOREIGN_KEYS=false
```

### Microsoft SQL Server Configuration

To configure **Tonka** Database for Microsoft SQL Server, follow these steps:

1. **Install the SQL Server Driver**: Ensure that the SQL Server driver is installed. You can read the documentation here [Microsoft SQL Server PDO Driver](https://learn.microsoft.com/fr-fr/sql/connect/php/pdo-query?view=sql-server-ver16).

2. **Environment Variables**: Use environment variables to manage your SQL Server credentials securely. Add the necessary variables to your `.env` file:
    ```env
    DB_CONNECTION=sqlsrv
    DB_HOST=127.0.0.1
    DB_PORT=1433
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

3. **Additional Configuration**: If you need to specify additional options, such as the character set or collation, you can add them to your configuration file:
    ```php
    'sqlsrv' => [
        'driver' => 'sqlsrv',
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '1433'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
    ],
    ```

Ensure that your SQL Server instance is configured to accept connections from your application and that the necessary firewall rules are in place.

### Configuration Using URLs

**Tonka** Database also supports configuring your database connections using URLs. This can simplify the configuration process by encapsulating all connection details in a single string. To use a URL for your database configuration, set the `DATABASE_URL` environment variable in your `.env` file:

```env
DB_URL=mysql://your_username:your_password@127.0.0.1:3306/your_database
```

Here are examples for other supported databases:

**PostgreSQL:**
```env
DB_URL=pgsql://your_username:your_password@127.0.0.1:5432/your_database
```

**SQLite:**
```env
DB_URL=sqlite:///absolute/path/to/your_database.sqlite
```

**SQL Server:**
```env
DB_URL=sqlsrv://your_username:your_password@127.0.0.1:1433/your_database
```

**Oracle:**
```env
DB_URL=oracle://your_username:your_password@127.0.0.1:1521/your_database
```

Ensure that the URL is properly formatted and includes all necessary connection details. This method can be particularly useful for managing different environments (development, staging, production) by simply changing the `DATABASE_URL` value.

## Running SQL Queries

Once you have configured your database connection, you may run queries using the [DB](https://github.com/clicalmani/database/blob/main/src/DB.php) facade. The `DB` facade provides methods for each type of query: `select`, `update`, `insert`, `delete`, and `statement`.

### Running a Select Query

To run a select query using **Tonka** Database, you can use the query builder or raw SQL. Here are examples of both methods:

#### Using the Query Builder

The [query builder](query_builder.md) provides a convenient, fluent interface for constructing SQL queries. Here's an example of how to use it to run a select query:

```php
$users = DB::table('users')->get('name, email');
```

This will generate and execute the following SQL query:

```sql
SELECT name, email FROM users;
```

#### Using Raw SQL

If you prefer to write raw SQL queries, you can use the `DB::select` method. Here's an example:

```php
$users = DB::select('SELECT name, email FROM users');
```

This method returns an array of results where each result is an instance of the PHP [stdClass](https://www.php.net/manual/en/class.stdclass.php) object.

#### Fetching a Single Row

If you need to fetch a single row from the database, you can use the `first` method with the query builder:

```php
$user = DB::table('users')->get('name, email')->first();
```

Or with raw SQL:

```php
$user = DB::selectOne('SELECT name, email FROM users LIMIT 1');
```

#### Using Named Bindings

Named bindings provide a way to safely pass parameters to your SQL queries, preventing SQL injection attacks. You can use named bindings with both the query builder and raw SQL.

##### Using Named Bindings with the Query Builder

Here's an example of using named bindings with the query builder:

```php
$users = DB::table('users')
    ->where('name = ?', ['John'])
    ->get();
```

This will generate and execute the following SQL query:

```sql
SELECT * FROM users WHERE name = ?';
```

##### Using Named Bindings with Raw SQL

You can also use named bindings with raw SQL queries. Here's an example:

```php
$users = DB::select('SELECT * FROM users WHERE name = ?', ['John']);
```

This method ensures that the `?` placeholder is safely replaced with the value `'John'`.

Using named bindings helps to keep your queries secure and your code clean.

### Running a General Statement

To execute a general SQL statement that does not fit into the select, insert, update, or delete categories, you can use the `DB::statement` method. This method allows you to run any arbitrary SQL statement.

Here's an example of running a general SQL statement:

```php
DB::statement('ALTER TABLE users ADD COLUMN age INT');
```

This method is useful for executing database schema changes or other operations that are not covered by the other query methods.

Using the `DB::statement` method, you can execute any SQL statement directly on your database.

### Running an Unprepared Statement

In some cases, you may need to run an unprepared statement. Unprepared statements are executed directly without any parameter binding or escaping, which can be useful for certain types of queries. However, be cautious when using unprepared statements, as they can expose your application to SQL injection attacks if not used properly.

To run an unprepared statement, you can use the `DB::unprepared` method. Here's an example:

```php
DB::unprepared('UPDATE users SET email = "unprepared@example.com" WHERE id = 1');
```

This method executes the given SQL query directly on the database without any parameter binding or escaping.

Use unprepared statements only when absolutely necessary and ensure that the input is safe and sanitized to prevent SQL injection vulnerabilities.

### Implicit Commits

**Tonka** Database supports implicit commits for certain types of SQL statements. An implicit commit occurs automatically when a statement that modifies the database is executed. This ensures that the changes are saved without requiring an explicit `COMMIT` statement.

#### Statements That Cause Implicit Commits

The following types of statements typically cause implicit commits:

- Data Definition Language (DDL) statements such as `CREATE`, `ALTER`, `DROP`, and `TRUNCATE`.
- Certain Data Manipulation Language (DML) statements like `INSERT`, `UPDATE`, and `DELETE` when executed outside of an explicit transaction.

#### Example of Implicit Commit

Here's an example of a DDL statement causing an implicit commit:

```php
DB::statement('CREATE TABLE new_table (id INT PRIMARY KEY, name VARCHAR(255))');
```

In this example, the `CREATE TABLE` statement will automatically commit the transaction, saving the changes to the database.

#### Managing Transactions

If you need more control over transactions, you can use explicit transactions to group multiple statements into a single transaction. This allows you to commit or roll back the changes as needed.

Here's an example of using explicit transactions:

```php
DB::transaction();

try {
    DB::table('users')->insert([['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']]);
    DB::table('profiles')->insert([['user_id' => 1, 'bio' => 'Software Developer']]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

In this example, the `transaction`, `commit`, and `rollBack` methods are used to manage the transaction explicitly, ensuring that both insert statements are executed as a single unit of work.

You can also pass a callback function to the `transaction` method to handle transactions more conveniently. This approach ensures that the transaction is automatically committed if the callback function returns true, or rolled back if the callback function returns false.

Here's an example of using a callback function with `transaction`:

```php
DB::transaction(function () {
    try {
        DB::table('users')->insert(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);
        DB::table('profiles')->insert(['user_id' => 1, 'bio' => 'Software Developer']);
        return true;
    } catch (\PDOException $e) {
        return false;
    }
});
```

## Using Multiple Database Connections

**Tonka** Database allows you to configure and use multiple database connections within your application. This can be useful for applications that need to interact with different databases or separate read and write operations.

### Configuring Multiple Connections

To configure multiple database connections, you need to define them in your database configuration file. Here's an example of how to define multiple connections:

```php
return [

    'default' => env('DB_CONNECTION', 'mysql'),

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

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST_PGSQL', '127.0.0.1'),
            'port' => env('DB_PORT_PGSQL', '5432'),
            'database' => env('DB_DATABASE_PGSQL', 'forge'),
            'username' => env('DB_USERNAME_PGSQL', 'forge'),
            'password' => env('DB_PASSWORD_PGSQL', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

];
```

In this example, two connections are defined: `mysql` and `pgsql`. You can add as many connections as needed, each with its own configuration.

### Using Multiple Connections

Once you have configured multiple connections, you can use them in your application by specifying the connection name. Here's an example of how to use a specific connection:

#### With the Query Builder

```php
$users = DB::connection('pgsql')->table('users')->get();
```

In this example, the `pgsql` connection is used to query the `users` table.

You may access the raw, underlying PDO instance of a connection using the `getPdo` method on a connection instance. This can be useful if you need to perform low-level database operations that are not supported by the query builder or **Elegant ORM**.

Here's an example of how to access the PDO instance:

```php
$pdo = DB::connection()->getPdo();
```

You can also access the PDO instance for a specific connection:

```php
$pdo = DB::connection('pgsql')->getPdo();
```

Once you have the PDO instance, you can use it to perform any PDO operations directly:

```php
$statement = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$statement->execute(['id' => 1]);
$user = $statement->fetch();
```

Using the `getPdo` method, you have full control over the database connection and can execute any PDO operations as needed.

#### Using **Tonka** Database ORM

If you are using **Elegant ORM**, you can specify the connection for a model by defining the `$connection` property:

```php
class User extends Elegant
{
    protected $connection = 'pgsql';
}
```

Now, all queries for the `User` model will use the `pgsql` connection.

### Switching Connections at Runtime

You can also switch connections at runtime using the `setConnection` method:

```php
$user = new User;
$user->setConnection('pgsql')->find(1);
```

This allows you to dynamically change the connection for a specific query or operation.

Using multiple database connections provides flexibility and allows your application to interact with different databases seamlessly.

## Listening for Query Events

**Tonka** Database provides the ability to listen for query events, which can be useful for debugging, logging, or monitoring database queries. You can register a listener for query events using the `listen` method on the `DB` facade.

### Registering a Query Listener

To register a query listener, you can use the following code:

```php
DB::listen(function ($query) {
    // $query->sql contains the SQL query
    // $query->bindings contains the query bindings
    // $query->profile contains the query profile
    Log::info(['Query executed: ' . $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->profile,
    ], __FILE__, __LINE__);
});
```

In this example, the query listener logs the executed SQL query, its bindings, and its profile using the `Log` facade.

### Example Usage

You can register the query listener in a service provider, such as the `AppServiceProvider`:

```php
use Clicalmani\Foundation\Support\Facades\DB;
use Clicalmani\Foundation\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DB::listen(function ($query) {
            Log::debug(['Query executed: ' . $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ], __FILE__, __LINE__);
        });
    }
}
```

By registering the query listener in the `boot` method of a service provider, you ensure that it is active for all database queries executed by your application.

Listening for query events can help you gain insights into the performance and behavior of your database queries, making it easier to optimize and debug your application.

## Monitoring Cumulative Query Time

**Tonka** Database allows you to monitor the cumulative query time for your application. This can help you identify performance bottlenecks and optimize your database interactions.

### Registering a Cumulative Query Time Listener

To monitor the cumulative query time, you can register a listener that tracks the total execution time of all queries. Here's an example:

```php
use Clicalmani\Foundation\Support\Facades\DB;
use Clicalmani\Foundation\Support\Facades\Log;

class QueryTimeTracker
{
    protected $totalQueryTime = 0;

    public function register()
    {
        DB::listen(function ($query) {
            $this->totalQueryTime = 0;
            foreach ($query->profile as $record) $this->totalQueryTime += $record['Duration'];
            Log::info('Cumulative query time: ' . $this->totalQueryTime . 'ms');
        });
    }
}
```

### Example Usage Of Cumulative Query Time Listener

You can register the cumulative query time listener in a service provider, such as the `AppServiceProvider`:

```php
use Clicalmani\Foundation\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $tracker = new QueryTimeTracker();
        $tracker->register();
    }
}
```

By registering the cumulative query time listener in the `boot` method of a service provider, you ensure that it tracks the total execution time of all database queries executed by your application.

Monitoring the cumulative query time can help you understand the overall performance impact of your database queries and identify areas for optimization.

## Database Transactions

**Tonka** Database provides robust support for database transactions, allowing you to execute a series of operations as a single unit of work. Transactions ensure that either all operations are successfully executed, or none of them are, maintaining the integrity of your data.

### Using Transactions

To start a transaction, you can use the `DB::beginTransaction` or `DB::transaction` method. Once the transaction is started, you can execute your database operations. If all operations are successful, you can commit the transaction using the `DB::commit` method. If any operation fails, you can roll back the transaction using the `DB::rollBack` method.

Here's an example of using transactions:

```php
DB::beginTransaction();

try {
    DB::table('users')->insert([
        ['name' => 'John Doe', 'email' => 'john.doe@example.com'],
        // Add more rows
    ]);
    DB::table('profiles')->insert([
        ['user_id' => 1, 'bio' => 'Software Developer'],
        // Add more rows
    ]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

In this example, both insert operations are executed within a transaction. If any operation fails, the transaction is rolled back, and no changes are made to the database.

Both the mothods `beginTransaction` and `transaction` method accept a callback function. The transaction is rolled back if the callback function returns a falsely value or an exception occures.

Here's an example of using the `transaction` method:

```php
DB::transaction(function () {
    DB::table('users')->insert(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);
    DB::table('profiles')->insert(['user_id' => 1, 'bio' => 'Software Developer']);
    return true;
});
```

### Save Points

Save points allow you to create intermediate points within a transaction that you can roll back to without rolling back the entire transaction. This can be useful for isolating portions of your code within a transaction.

Here's an example of using save points:

```php
DB::beginTransaction();

try {
    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
    
    // Create a save point
    DB::savepoint('savepoint1');
    
    DB::table('profiles')->insert(['user_id' => 1, 'bio' => 'Software Developer']);
    
    // Roll back to the save point if needed
    DB::rollbackTo('savepoint1');
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

In this example, a save point named `savepoint1` is created after inserting a user. If an error occurs while inserting the profile, the transaction can be rolled back to `savepoint1` without rolling back the entire transaction.

In this example, the transaction is automatically committed if both insert operations are successful. If an exception is thrown, the transaction is rolled back.

### Setting the Transaction Isolation Level

**Tonka** Database allows you to set the transaction isolation level to control the visibility of changes made by other transactions. The isolation level determines how transaction integrity is maintained and can help prevent issues such as dirty reads, non-repeatable reads, and phantom reads.

To set the transaction isolation level, you can use the `DB::statement` method to execute the appropriate SQL command. Here are examples of setting different isolation levels:

#### Read Uncommitted

```php
DB::statement('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
```

This isolation level allows transactions to read uncommitted changes made by other transactions, which can lead to dirty reads.

#### Read Committed

```php
DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
```

This isolation level ensures that transactions can only read committed changes made by other transactions, preventing dirty reads.

#### Repeatable Read

```php
DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
```

This isolation level ensures that if a transaction reads a row, subsequent reads within the same transaction will return the same data, preventing non-repeatable reads.

#### Serializable

```php
DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
```

This isolation level ensures complete isolation from other transactions, preventing dirty reads, non-repeatable reads, and phantom reads.

You can set the isolation level at the beginning of a transaction to ensure that it applies to all operations within that transaction. Here's an example:

```php
DB::beginTransaction();

try {
    DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

In this example, the transaction isolation level is set to `SERIALIZABLE` before executing any operations within the transaction.

By setting the appropriate transaction isolation level, you can control the visibility of changes and maintain the integrity of your transactions.

### Handling Deadlocks

When working with transactions, it's important to handle potential deadlocks. A deadlock occurs when two or more transactions are waiting for each other to release locks, resulting in a stalemate. To handle deadlocks, you can call the `deadlock` method.

Here's an example of handling deadlocks:

```php
DB::deadlock(function () {
    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
    DB::table('profiles')->insert(['user_id' => 1, 'bio' => 'Software Developer']);
})
```

In this example, the transaction is retried up to five times if a deadlock occurs. This approach helps ensure that your application can recover from deadlocks and complete the transaction successfully.

Using transactions in **Tonka** Database helps maintain data integrity and consistency, ensuring that your database operations are executed reliably.

## Connecting to the Database CLI

**Tonka** Database provides a command-line interface (CLI) that allows you to interact with your database directly from the terminal. This can be useful for running queries, managing database migrations, and performing other administrative tasks.

### Installing the CLI

To install the **Tonka** Database CLI, you can use your package manager. For example, if you are using Composer, you can install it with the following command:

```bash
composer require tonka/database-cli
```

### Using the CLI

Once the CLI is installed, you can use it to connect to your database and run commands. Here are some common commands you can use:

#### Connecting to the Database

To connect to your database, use the `connect` command followed by the connection name:

```bash
tonka db:connect mysql
```

This will connect to the `mysql` database connection defined in your configuration file.

#### Running SQL Queries in the CLI

You can run SQL queries directly from the CLI using the `query` command:

```bash
tonka db:query "SELECT * FROM users"
```

This will execute the given SQL query and display the results in the terminal.

#### Managing Migrations

The CLI also provides commands for managing database migrations. Here are some examples:

- To run all pending migrations:

    ```bash
    tonka db:migrate
    ```

- To roll back the last batch of migrations:

    ```bash
    tonka db:rollback
    ```

- To create a new migration file:

    ```bash
    tonka db:make:migration create_users_table
    ```

### CLI Help

For a full list of available commands and options, you can use the `help` command:

```bash
tonka db:help
```

This will display detailed information about each command and how to use it.

Using the **Tonka** Database CLI, you can efficiently manage your database and perform various tasks directly from the terminal.

## Inspecting Your Databases

**Tonka** Database provides several tools to help you inspect and manage your databases. These tools allow you to view the structure of your databases, analyze query performance, and monitor database activity.

### Viewing Table Structure

To view the structure of a table, you can use the `describe` command in the **Tonka** Database CLI. This command provides detailed information about the columns, data types, and indexes of a table.

```bash
tonka db:describe users
```

This will display the structure of the `users` table, including column names, data types, and any indexes or constraints.

### Analyzing Query Performance

**Tonka** Database includes tools for analyzing the performance of your queries. You can use the `explain` command to get detailed information about how a query is executed, including the query plan and any potential performance bottlenecks.

```bash
tonka db:explain "SELECT * FROM users WHERE email = 'john.doe@example.com'"
```

This will display the query plan for the given SQL query, helping you identify any areas for optimization.

### Monitoring Database Activity

To monitor database activity, you can use the `monitor` command in the **Tonka** Database CLI. This command provides real-time information about active connections, running queries, and other database metrics.

```bash
tonka db:monitor
```

This will display real-time information about your database, allowing you to monitor its performance and activity.

### Viewing Database Logs

**Tonka** Database also allows you to view database logs, which can be useful for debugging and auditing purposes. You can use the `logs` command to view recent database logs.

```bash
tonka db:logs
```

This will display recent logs from your database, including any errors or warnings.

Using these tools, you can effectively inspect and manage your databases, ensuring optimal performance and reliability.