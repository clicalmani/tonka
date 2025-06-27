- [Introduction](collection.md?id=introduction)

## Introduction

**Tonka** Framework's collection module provides a set of powerful, flexible data structures designed to simplify data management in your applications. With built-in support for common operations such as filtering, sorting, and transformation, the collection module enables developers to handle complex datasets with ease. Whether you're working with arrays, maps, or custom data types.


## Table of Contents

- [Getting Started](#getting-started)
- [Core Concepts](#core-concepts)
- [Available Data Structures](#available-data-structures)
    - [ArrayCollection](#arraycollection)
    - [MapCollection](#mapcollection)
    - [Custom Collections](#custom-collections)
- [Common Operations](#common-operations)
    - [Filtering](#filtering)
    - [Sorting](#sorting)
    - [Transformation](#transformation)
- [Usage Examples](#usage-examples)
- [Best Practices](#best-practices)
- [API Reference](#api-reference)
- [FAQ](#faq)
- [Changelog](#changelog)

## Getting Started

To begin using the **Tonka** Framework's collection module, you can create a collection instance either by using the `collection` helper function or by directly importing the `Clicalmani\Foundation\Collection\Collection` class.

#### Using the Collection Helper

```php
$collection = collection([1, 2, 3, 4]);
```

#### Importing the Collection Class

```php
use Clicalmani\Foundation\Collection\Collection;

$collection = new Collection([1, 2, 3, 4]);
```

Both approaches provide access to the full set of collection methods for managing and manipulating your data.

#### Performing Operations

Collections provide methods for filtering, sorting, and transforming data:

```php
User::all()->filter(fn(User $user) => $user->isAdmin());
User::all()->sortBy('name');
```

## Core Concepts

The collection module is built around a few key principles:

- **Mutability:** Collections are designed to be mutable, by default—most operations modify the original instance.
- **Chainability:** Methods can be chained for expressive and concise data manipulation.
- **Type Flexibility:** Collections can handle arrays, objects, or custom data types.

Understanding these concepts will help you leverage the full power of the collection module in your applications.

## Available Data Structures

Tonka's collection module offers several built-in data structures to accommodate different use cases:

### ArrayCollection

A flexible wrapper around PHP arrays, providing advanced methods for manipulation, searching, and transformation.

```php
use Clicalmani\Foundation\Collection\ArrayCollection;

$arrayCollection = new ArrayCollection([1, 2, 3, 4]);
```

### MapCollection

A key-value store similar to associative arrays or maps, supporting efficient lookups and advanced operations.

```php
use Clicalmani\Foundation\Collection\MapCollection;

$mapCollection = new MapCollection(['name' => 'Alice', 'age' => 30]);
```

### Custom Collections

You can extend the base `Collection` class to create your own custom data structures tailored to your application's needs.

```php
use Clicalmani\Foundation\Collection\Collection;

class UserCollection extends Collection
{
    // Add custom methods or overrides here
}
```

Each data structure provides a consistent API, making it easy to switch between them as your requirements evolve.

## Common Operations

Collections in Tonka provide a rich set of methods for manipulating and querying data. Here are some of the most commonly used operations:

### Filtering

Filter elements based on a condition using the `filter` method:

```php
$adults = $users->filter(fn($user) => $user['age'] >= 18);
```

### Sorting

Sort collections by a specific field or custom logic:

```php
$sorted = $users->sortBy('name');
$sortedDesc = $users->sortByDesc('created_at');
```

### Transformation

Transform each element using the `map` method:

```php
$names = $users->map(fn($user) => $user['name']);
```

### Aggregation

Aggregate data with methods like `sum`, `count`, or `reduce`:

```php
$total = $orders->sum('amount');
$count = $users->count();
```

### Searching

Find elements with `find`, `first`, or `contains`:

```php
$firstAdmin = $users->first(fn($user) => $user['role'] === 'admin');
$hasAlice = $users->contains('name', 'Alice');
```

These operations can be chained for expressive and concise data manipulation:

```php
$names = $users
    ->filter(fn($user) => $user['active'])
    ->sortBy('name')
    ->map(fn($user) => $user['name']);
```

## Usage Examples

Here are some practical examples demonstrating how to use the Tonka collection module in real-world scenarios.

### Example 1: Filtering and Mapping Users

```php
$users = collection([
    ['name' => 'Alice', 'age' => 25, 'active' => true],
    ['name' => 'Bob', 'age' => 17, 'active' => false],
    ['name' => 'Charlie', 'age' => 30, 'active' => true],
]);

$activeAdults = $users
    ->filter(fn($user) => $user['active'] && $user['age'] >= 18)
    ->map(fn($user) => $user['name']);

// $activeAdults contains ['Alice', 'Charlie']
```

### Example 2: Sorting and Reducing Orders

```php
$orders = collection([
    ['id' => 1, 'amount' => 50],
    ['id' => 2, 'amount' => 100],
    ['id' => 3, 'amount' => 75],
]);

$totalAmount = $orders
    ->sortByDesc('amount')
    ->sum('amount');

// $totalAmount is 225
```

### Example 3: Working with MapCollection

```php
use Clicalmani\Foundation\Collection\MapCollection;

$settings = new MapCollection([
    'theme' => 'dark',
    'notifications' => true,
]);

$hasTheme = $settings->contains('theme'); // true
```

These examples illustrate how collections can simplify data processing and improve code readability.

## Best Practices

To get the most out of the Tonka collection module, consider the following best practices:

- **Prefer Immutability:** Chain methods to avoid mutating the original collection, which helps prevent unintended side effects.
- **Use Type Consistency:** Store similar data types within a collection to simplify operations and reduce errors.
- **Leverage Method Chaining:** Combine multiple operations in a single, fluent chain for concise and readable code.
- **Extend for Custom Logic:** Create custom collection classes for domain-specific behaviors or validation.
- **Handle Empty Collections Gracefully:** Use methods like `firstOrNull()` or check for emptiness to avoid exceptions.
- **Document Custom Methods:** When extending collections, document any custom methods for maintainability.

Following these practices will help you write robust, maintainable, and expressive code with the Tonka collection module.


## API Reference

Below is a summary of the most commonly used methods available in the Tonka collection module. For detailed parameter and return type information, refer to the official documentation.

### Collection Methods

| Method                | Description                                                      |
|-----------------------|------------------------------------------------------------------|
| `all()`               | Returns all items in the collection as an array.                 |
| `filter(callable)`    | Filters items using a callback and returns a new collection.     |
| `map(callable)`       | Transforms each item using a callback and returns a new collection. |
| `reduce(callable, $initial)` | Reduces the collection to a single value.                |
| `sortBy($key)`        | Sorts items by a given key or callback.                          |
| `sortByDesc($key)`    | Sorts items by a given key or callback in descending order.      |
| `first(callable?)`    | Returns the first item matching a condition, or the first item.  |
| `find($key, $default?)` | Finds an item by key, or returns default if not found.         |
| `contains($key, $value?)` | Checks if the collection contains a key or value.            |
| `sum($key?)`          | Returns the sum of values for a given key or all items.          |
| `count()`             | Returns the number of items in the collection.                   |
| `isEmpty()`           | Checks if the collection is empty.                               |
| `toArray()`           | Converts the collection to a plain array.                        |

### ArrayCollection & MapCollection

These classes inherit all base collection methods and may provide additional methods specific to their data structure, such as:

- `keys()` — Returns all keys in the collection.
- `values()` — Returns all values in the collection.
- `get($key, $default?)` — Retrieves a value by key (MapCollection).

### Custom Collections

When extending the base `Collection` class, you can add your own domain-specific methods as needed.

For a complete list of methods and advanced usage, consult the [Tonka Framework documentation](https://tonka-framework-docs.example.com).

## FAQ

### What PHP version is required for the Tonka collection module?
Tonka requires PHP 8.0 or higher.

### Can I use collections with custom objects?
Yes, collections can store and manipulate any data type, including custom objects.

### Are collections mutable?
Most collection methods return new instances, leaving the original collection unchanged.

### How do I convert a collection back to a plain array?
Use the `toArray()` method.

### Can I extend the collection classes?
Yes, you can extend the base `Collection` class to add custom methods or behaviors.

### Where can I find more examples?
See the [Usage Examples](#usage-examples) section or visit the [official documentation](https://tonka-framework-docs.example.com).

