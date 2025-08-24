- [Introduction](entities?id=introduction)
- [How To Create An Entity?](entities?id=how-to-create-an-entity)
- [Entity Data Types](entities?id=entity-data-types)
    - [Numeric Data Types](entities?id=numeric-data-types)
        - [Integer](entities?id=integer)
        - [Medium Integer](entities?id=medium-integer)
        - [Big Integer](entities?id=big-integer)
        - [Small Integer](entities?id=small-integer)
        - [Tiny Integer](entities?id=tiny-integer)
        - [Decimal](entities?id=decimal)
        - [Fixed](entities?id=fixed)
        - [Double](entities?id=double)
    - [String Data Type](entities?id=string-data-type)
        - [Char](entities?id=char)
        - [VarChar](entities?id=varchar)
        - [Text](entities?id=text)
        - [Tiny Text](entities?id=tiny-text)
        - [Long Text](entities?id=long-text)
        - [Tiny Blob](entities?id=tiny-blob)
        - [Medium Blob](entities?id=medium-blob)
        - [Long Blob](entities?id=long-blob)
        - [Binary](entities?id=binary)
        - [CharByte](entities?id=charbyte)
        - [VarBinary](entities?id=varbinary)
        - [Blob](entities?id=blob)
        - [Enum](entities?id=enum)
        - [Set](entities?id=set)
    - [Date And Time Data Types](entities?id=date-and-time-data-types)
        - [Date](entities?id=date)
        - [Date Time](entities?id=date-time)
        - [Timestamp](entities?id=timestamp)
    - [Json Data Type](entities?id=json-data-type)
    - [Indexes](entities?id=indexes)
        - [Unique Index](entities?id=unique-index)
        - [Foreign Keys](entities?id=foreign-keys)

## Introduction

An entity is an object that exists. It doesn't have to do anything, it just has to exist. In database administration, an entity can be a thing, a person, a place, or a single object. Data can be stored about these entities. An entity contains attributes, which describe that entity. So anything about which we store information is called an entity.

**Tonka** uses entities to store or manipulate data. A **Tonka** entity is a class that extends the [Clicalmani\Database\Factory\Entity](https://github.com/clicalmani/database/factory/entity) class, the underlying table attributes will be defined as public properties of the class.

## How To Create An Entity?

The `db:entity` console command allows the creation of a database entity. It accepts a single argument which is the name of the entity to create.

```sh
php tonka db:entity Post
```

The above command will create the `PostEntity.php` file. When you dive into `database/entities` you will see the content of the file as show here:

```php
<?php
<?php
namespace Database\Entities;

use Clicalmani\Database\Factory\Entity;

class PostEntity extends Entity
{
    // ...
}
?>
```

!> **Tonka** establishes a relationship between a `model` and its underlying `entity` that would require the model to be attached to its underlying entity. This relationship would like the entity to be named by having "Entity" attached to its model name such as `UserEntity` for `User` model.

## Entity Data Types

A data type in **Tonka** is a class that extends `DataType` class representing a SQL corresponding data type. Most of the types bear the same name such as `varchar`, `char`, `enum`... 

In most SQL languages there are three main data types: string, numeric, and date and time.

### Numeric Data Types

Numeric data types are fundamental to database design and are used to store numbers, whether they are integers, decimals, or floating-point numbers.

#### Integer

You may use the `Integer` class to represent `INT` or `INTEGER` data type:

```php
use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 10,
    unsigned: true,
    nullable: false
)]
public Integer $id;
```

#### Medium Integer

You may use the `MediumInt` class to represent `MEDIUMINT` data type:

```php
use Clicalmani\Database\DataTypes\MediumInt;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 10,
    unsigned: true,
    nullable: false
)]
public MediumInt $id;
```

#### Big Integer

You may use the `BigInt` class to represent `BIGINT` data type:

```php
use Clicalmani\Database\DataTypes\BigInt;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 20,
    unsigned: true,
    nullable: false
)]
public BigInt $id;
```

#### Small Integer

You may use the `SmallInt` class to represent `SMALLINT` data type:

```php
use Clicalmani\Database\DataTypes\SmallInt;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 20,
    unsigned: true,
    nullable: false
)]
public SmallInt $id;
```

#### Tiny Integer

You may use the `TinyInt` class to represent `TINYINT` data type:

```php
use Clicalmani\Database\DataTypes\TinyInt;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 20,
    unsigned: true,
    nullable: false
)]
public TinyInt $id;
```

#### Decimal

You may use the `Decimal` class to represent `DECIMAL` data type:

```php
use Clicalmani\Database\DataTypes\Decimal;
use Clicalmani\Database\Factory\Property;

#[Property(
    precision: 10,
    scale: 2,
    nullable: false
)]
public Decimal $price;
```

#### Fixed

You may use the `Fixed` class to represent `FIXED` data type:

```php
use Clicalmani\Database\DataTypes\Fixed;
use Clicalmani\Database\Factory\Property;

#[Property(
    precision: 10,
    scale: 2,
    nullable: false
)]
public Fixed $price;
```

#### Double

You may use the `Double` class to represent `DOUBLE` data type:

```php
use Clicalmani\Database\DataTypes\Double;
use Clicalmani\Database\Factory\Property;

#[Property(
    precision: 10,
    scale: 2,
    nullable: false
)]
public Double $price;
```

### String Data Type

String data types are used to store text or character-based data.

#### Char

You may use the `Char` class to represent `CHAR` data type:

```php
use Clicalmani\Database\DataTypes\Char;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 5,
    nullable: false
)]
public Char $price;
```

#### VarChar

You may use the `VarChar` class to represent `VARCHAR` data type:

```php
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Property;

#[Property(
    length: 5,
    nullable: false
)]
public VarChar $price;
```

#### Text

You may use the `Text` class to represent `TEXT` data type:

```php
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public Text $price;
```

#### Tiny Text

You may use the `TinyText` class to represent `TINYTEXT` data type:

```php
use Clicalmani\Database\DataTypes\TinyText;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public TinyText $price;
```

#### Long Text

You may use the `LongText` class to represent `LONGTEXT` data type:

```php
use Clicalmani\Database\DataTypes\LongText;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public LongText $price;
```

#### Tiny Blob

You may use the `TinyBlob` class to represent `TINYBLOB` data type:

```php
use Clicalmani\Database\DataTypes\TinyBlob;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public TinyBlob $price;
```

#### Medium Blob

You may use the `MediumBlob` class to represent `MEDIUMBLOB` data type:

```php
use Clicalmani\Database\DataTypes\MediumBlob;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public MediumBlob $price;
```

#### Long Blob

You may use the `LongBlob` class to represent `LONGBLOB` data type:

```php
use Clicalmani\Database\DataTypes\LongBlob;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public LongBlob $price;
```

#### Binary

You may use the `Binary` class to represent `BINARY` data type:

```php
use Clicalmani\Database\DataTypes\Binary;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public Binary $price;
```

#### CharByte

You may use the `CharByte` class to represent `CHARBYTE` data type:

```php
use Clicalmani\Database\DataTypes\CharByte;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public CharByte $price;
```

#### VarBinary

You may use the `VarBinary` class to represent `VARBINARY` data type:

```php
use Clicalmani\Database\DataTypes\VarBinary;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public VarBinary $price;
```

#### Blob

You may use the `Blob` class to represent `BLOB` data type:

```php
use Clicalmani\Database\DataTypes\Blob;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public Blob $price;
```

#### Enum

You may use the `Enum` class to represent `ENUM` data type:

```php
use Clicalmani\Database\DataTypes\Enum;
use Clicalmani\Database\Factory\Property;

#[Property(
    values: ['Value 1', 'Value 2', ...]
    nullable: false,
    default: 'Value 1'
)]
public Enum $price;
```

#### Set

You may use the `Set` class to represent `SET` data type:

```php
use Clicalmani\Database\DataTypes\Set;
use Clicalmani\Database\Factory\Property;

#[Property(
    values: ['Value 1', 'Value 2', ...]
    nullable: false,
    default: 'Value 1'
)]
public Set $price;
```

### Date And Time Data Types

#### Date

You may use the `Date` class to represent `DATE` data type:

```php
use Clicalmani\Database\DataTypes\Date;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public Date $price;
```

#### Date Time

You may use the `DateTime` class to represent `DATETIME` data type:

```php
use Clicalmani\Database\DataTypes\DateTime;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public DateTime $price;
```

#### Timestamp

You may use the `Timestamp` class to represent `TIMESTAMP` data type:

```php
use Clicalmani\Database\DataTypes\Timestamp;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public Timestamp $price;
```

### Json Data Type

You may use the `Json` class to represent `JSON` data type:

```php
use Clicalmani\Database\DataTypes\Json;
use Clicalmani\Database\Factory\Property;

#[Property(
    nullable: false
)]
public Json $price;
```

Attributes with Json data type are automatically processed on reading or writing by calling `json_encode` and `json_decode` function respectively. You may configure how Json data may be treated by customizing json configuration options in `config/app` file:

```php
/*
 |---------------------------------------------------------------------------
 | JSON 
 |---------------------------------------------------------------------------
 | 
 | This settings instruct how to encode and decode json string and convert it 
 | into a PHP value.
*/

'json' => [
    'encode' => [
        'flags' => JSON_UNESCAPED_UNICODE,
        'depth' => 512
    ],
    'decode' => [
        'associative' => true,
        'depth' => 512,
        'flags' => JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
    ]
]
```

### Indexes

The `Index` class is dedicated to represent indexes. It will be used as class attribute on entity classes:

#### Unique Index

```php
<?php
namespace Database\Entities;

use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;
use Clicalmani\Database\Factory\DataTypes\VarChar;

#[Index(
    name: 'nameUNIQUE',
    key: 'name',
    unique: true
)]
class Category extends Entity
{
    #[Property(
        length: 100,
        nullable: false
    )]
    public VarChar $name;
}
?>
```

!> If index is composed with more than one key, separate them with commas.

#### Foreign Keys

A constraint index (foreign key) may be specified as follow:

```php
<?php
namespace Database\Entities;

use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;
use Clicalmani\Database\Factory\DataTypes\Integer;

#[Index(
    name: 'fk_user_role1_idx',
    key: 'role',
    constraint: 'fk_user_role1',
    references: \App\Models\Role::class,
    onUpdate: Index::ON_UPDATE_CASCADE,
    onDelete: Index::ON_DELETE_CASCADE
)]
class User extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        nullable: false
    )]
    public Integer $role;
}
?>
```

!> `onUpdate` and `onDelete` arguments default to `Index::ON_UPDATE_CASCADE` and `Index::ON_DELETE_CASCADE` respectively, if not specified.
