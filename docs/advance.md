**Advance database**

## Stored procedures

Procedures are stored functions that accept one or more arguments but do not return values. The `db:procedure` command allows the creation of a procedure in the database.

```bash
php tonka db:procedure <procedure_name>
```

After launching the command, a file with the name of the procedure will be created in the `database/routines/procedures` directory and containing the minimal code for creating the procedure.

```php
<?php
/**
 * Please do not modify the passed argument, because it will be read internally to set database tables prefix.
 * Do remember your code will be executed in a sandbox environment. The internal container will not interact with 
 * your code. Keep it clean !
 */
return function(string $table_prefix = '%DB_TABLE_PREFIX%') : string {
    return <<<SQL
        CREATE PROCEDURE `procedure_name`() 
        READS SQL DATA
        BEGIN
            
        END;
    SQL;
};

```
Let's take the example of a simple procedure that displays articles published during a period.

```php
<?php
/**
 * Please do not modify the passed argument, because it will be read internally to set database tables prefix.
 * Do remember your code will be executed in a sandbox environment. The internal container will not interact with 
 * your code. Keep it clean !
 */
return function(string $table_prefix = '%DB_TABLE_PREFIX%') : string {
    return <<<SQL
        CREATE PROCEDURE `postPublishedDuring`(IN start_date DATE, IN end_date DATE) 
        READS SQL DATA
        BEGIN
            SELECT * FROM `post` WHERE `publish_date` BETWEEN start_date AND end_date;
        END;
    SQL;
};
```

## Stored functions

Stored functions behave exactly like stored procedures except that they can return a value. The console command that allows you to create a function is `db:function`.

```bash
php tonka db:function function_name
```

Let's take the example of a simple calculation of the average spending on a product in the store:

```php
<?php
/**
 * Please do not modify the passed argument, because it will be read internally to set database tables prefix.
 * Do remember your code will be executed in a sandbox environment. The internal container will not interact with 
 * your code. Keep it clean !
 */
return function(string $table_prefix = '%DB_TABLE_PREFIX%') : string {
    return <<<SQL
        CREATE FUNCTION `expensesAVG`(product INT) RETURNS DECIMAL(10,2)
        READS SQL DATA
            DECLARE sale_average DECIMAL(10,2);
            SELECT AVG('amount') average FROM `transaction` WHERE `product_id` = product INTO sale_average;
            RETURN sale_average;
        BEGIN
        END;
    SQL;
};
```

## Views

In reality a view is a table that contains the results of a stored query. It takes no arguments and does not return a value. To create a view, the `db:view` console command creates a file with the name of the view in the `database/Routines/views` folder.

```bash
php tonka db:view <view_name>
```

Let's take the example of a view that stores customers who have purchased at least once in the store.

```php
<?php
/**
 * Please do not modify the passed argument, because it will be read internally to set database tables prefix.
 * Do remember your code will be executed in a sandbox environment. The internal container will not interact with 
 * your code. Keep it clean !
 *
 * The placeholder table the your view can be created through migration command.
 */
return function(string $table_prefix = '%DB_TABLE_PREFIX%') : string {
    return <<<SQL
        CREATE  OR REPLACE VIEW `inventories` AS 
        SELECT SUM('amount') total, name customer FROM {$table_prefix}transaction
            LEFT JOIN {$table_prefix}customers USING('customer_id')
            GROUP BY customer
            HAVING total > 0;
    SQL;
};
```