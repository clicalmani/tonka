- [Introduction](migration?id=introduction)
- [How to do a Migration?](migration?id=how-to-do-a-migration)
- [Isolating Migration Execution](migration?id=isolating-migration-execution)
- [Rolling Back Migrations](migration?id=rolling-back-migrations)
- [Run Migration in Production](migration?id=run-migration-in-production)
- [Migrate a Single Database Table](migration?id=migrate-a-single-database-table)
    - [Creating New Table in the Database](migration?id=creating-new-table-in-the-database)
    - [Update an Existing Table in the Database](migration?id=update-an-existing-table-in-the-database)

## Introduction

Database migration in Tonka involves using data entities to seamlessly transfer and upgrade your database schema. Tonka provides automated migration files, ensuring that your database changes are version-controlled and easily reversible. This process helps maintain data integrity and minimizes downtime during updates.

## How to do a Migration?

To perform a migration in Tonka, you need to create entities that represent the changes you want to make to your database schema. These entities are defined using Tonka's schema definition language, which allows you to specify the structure and relationships of your data. Once you have defined your entities, you can generate migration files that will apply these changes to your database. Each migration file contains the necessary instructions to update your database schema in a controlled and reversible manner.

The generated migration files are saved in the `database/migrations` directory. This directory serves as a version-controlled repository of all the changes made to your database schema. By organizing your migrations in this way, Tonka ensures that you can easily track and manage the evolution of your database over time. Additionally, if you need to roll back a migration, Tonka provides tools to reverse the changes, helping you maintain data integrity and minimize downtime during updates.

To perform a fresh database migration, use the `migrate:fresh` command in the console:

```bash
php tonka migrate:fresh migration-file-name
```

The `migrate:fresh` command also supports additional options such as seeding and creating routines. You can use the following options:

- `--seed`: Automatically seed the database after running the migrations.
- `--create-routines`: Create necessary routines after running the migrations.

Example usage with options:

```bash
php tonka migrate:fresh migration-file-name --seed --create-routines
```

To save the generated SQL statement, you can specify the `--output` option with the `migrate:fresh` command. This option allows you to define the directory where the SQL statements will be saved.

Example usage:

```bash
php tonka migrate:fresh migration-file-name --output=output-file-name
```

By using the `--output` option, you can ensure that the SQL statements generated during the migration process are stored in a specified file for future reference or auditing purposes.

## Isolating Migration Execution

Tonka automatically places locks on tables to isolate each migration, ensuring that each migration runs independently without interference from other operations. This mechanism helps maintain data integrity and consistency during the migration process.

## Rolling Back Migrations

You can rollback to a previous migration by specifying its migration file as the current migration file. 
This allows you to undo changes made by subsequent migrations and revert the database schema to the state defined by the specified migration.

## Run Migration in Production

There are two different ways to perform the migration in production mode: 
1. Using SSH: Connect to the production server via SSH and run the migration commands directly.
2. Using the browser: Create a `/migrate` web route.

```php
use Clicalmani\Foundation\Routing\Route;
use Clicalmani\Foundation\Support\Facades\Tonka;

Route::get('/migrate', function() {
    return Tonka::migrateFresh( time() );
});
```

!> Ensure to remove the `/migrate` route after the migration is complete to prevent unauthorized access.

## Migrate a Single Database Table

To migrate a single database table without losing data, Tonka provides the `migrate:entity` command. This command allows you to apply changes to a specific table while preserving the existing data.

The `migrate:entity` command accepts the table model name as its unique argument. Models will be covered in the Models section.

Example usage:

```bash
php tonka migrate:entity User
```

By using the `migrate:entity` command, you can ensure that your data remains intact while updating the schema of a single table.

By migrating a single table you may be willing to create a new table or update an existing one:

### Creating New Table in the Database

To create a new table, you need to define the table entity and the table model, and then invoke the `migrate:entity` command.

First, create the table entity using `db:entity` command. This entity will define the structure and relationships of your new table.

Next, create the table model that corresponds to the table entity with `make:model` command. The model will be used to interact with the table in your application.

Finally, run the `migrate:entity` command to apply the changes to your database:

```bash
php tonka migrate:entity TableModelName
```

By following these steps, you can create a new table in your database while ensuring that the schema is properly defined and version-controlled.

### Update an Existing Table in the Database

To update an existing table, you need to add the `AlterOption` attribute to the table entity and then implement the `alter` method of the entity class.

First, add the `AlterOption` attribute to the table entity to specify the changes you want to make to the table structure.

Next, implement the `alter` method in the entity class. This method will contain the logic to apply the specified changes to the existing table.

Example:

```php
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\AlterOption;

#[AlterOption]
class CashierEntity extends Entity
{
    // Class properties ....


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function alter(AlterOption $table): string
    {
        $table->addColumn('user')->intUnsigned()->nullable(false)->end();
        $table->addIndex('fk_cashier_user1_idx', ['user'])->end();
        $table->addConstraint('fk_cashier_user1')->foreignKey(['user'])
            ->references('users', ['user_id'])
            ->onDeleteCascade()
            ->onUpdateCascade();

        return $table->render();
    }
}
```

Finally, run the `migrate:entity` command to apply the changes to the existing table:

```bash
php tonka migrate:entity User
```

By following these steps, you can update the schema of an existing table while preserving the existing data.
