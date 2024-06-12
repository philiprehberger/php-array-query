# PHP Array Query

[![Tests](https://github.com/philiprehberger/php-array-query/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-array-query/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-array-query.svg)](https://packagist.org/packages/philiprehberger/php-array-query)
[![Last updated](https://img.shields.io/github/last-commit/philiprehberger/php-array-query)](https://github.com/philiprehberger/php-array-query/commits/main)

SQL-like fluent query builder for filtering, sorting, and transforming arrays.

## Requirements

- PHP 8.2+

## Installation

```bash
composer require philiprehberger/php-array-query
```

## Usage

### Basic Filtering

```php
use PhilipRehberger\ArrayQuery\ArrayQuery;

$users = [
    ['name' => 'Alice', 'age' => 30, 'city' => 'NYC', 'score' => 85],
    ['name' => 'Bob', 'age' => 25, 'city' => 'LA', 'score' => 92],
    ['name' => 'Charlie', 'age' => 35, 'city' => 'NYC', 'score' => 78],
    ['name' => 'Diana', 'age' => 28, 'city' => 'Chicago', 'score' => null],
    ['name' => 'Eve', 'age' => 22, 'city' => 'LA', 'score' => 95],
];

$results = ArrayQuery::from($users)
    ->where('city', '=', 'NYC')
    ->where('age', '>', 25)
    ->get();
// [['name' => 'Alice', ...], ['name' => 'Charlie', ...]]
```

### Sorting

```php
$results = ArrayQuery::from($users)
    ->sort('age', 'desc')
    ->get();
// Sorted by age descending: Charlie, Alice, Diana, Bob, Eve
```

### Limit & Offset

```php
$results = ArrayQuery::from($users)
    ->sort('age')
    ->offset(1)
    ->limit(3)
    ->get();
// Skip first, take next 3
```

### Select Specific Keys

```php
$results = ArrayQuery::from($users)
    ->select(['name', 'age'])
    ->get();
// [['name' => 'Alice', 'age' => 30], ...]
```

### Pluck a Single Column

```php
$names = ArrayQuery::from($users)
    ->where('city', '=', 'LA')
    ->pluck('name');
// ['Bob', 'Eve']
```

### Group By

```php
$groups = ArrayQuery::from($users)
    ->groupBy('city');
// ['NYC' => [...], 'LA' => [...], 'Chicago' => [...]]
```

### Aggregates

```php
$query = ArrayQuery::from($users)->whereNotNull('score');

$query->sum('score');   // 350
$query->avg('score');   // 87.5
$query->min('score');   // 78
$query->max('score');   // 95
$query->count();        // 4
```

### Additional Filters

```php
// Where In
ArrayQuery::from($users)->whereIn('city', ['NYC', 'LA'])->get();

// Where Null / Not Null
ArrayQuery::from($users)->whereNull('score')->get();
ArrayQuery::from($users)->whereNotNull('score')->get();

// Where Between
ArrayQuery::from($users)->whereBetween('age', 25, 30)->get();

// Like (case-insensitive)
ArrayQuery::from($users)->where('name', 'like', '%ali%')->get();
```

### Distinct

```php
$items = [
    ['name' => 'Alice', 'city' => 'NYC'],
    ['name' => 'Bob', 'city' => 'LA'],
    ['name' => 'Charlie', 'city' => 'NYC'],
];

// Remove duplicates by city (keeps first occurrence)
ArrayQuery::from($items)->distinct('city')->get();
// [['name' => 'Alice', 'city' => 'NYC'], ['name' => 'Bob', 'city' => 'LA']]

// Remove fully duplicate rows
ArrayQuery::from($items)->distinct()->get();
```

### Chunk

```php
$results = ArrayQuery::from($users)
    ->sort('age')
    ->chunk(2);
// [[['name' => 'Eve', ...], ['name' => 'Bob', ...]], [['name' => 'Diana', ...], ['name' => 'Alice', ...]], [['name' => 'Charlie', ...]]]
```

### Contains Operator

```php
$items = [
    ['name' => 'Alice', 'tags' => ['php', 'javascript']],
    ['name' => 'Bob', 'tags' => ['python', 'go']],
];

ArrayQuery::from($items)->where('tags', 'contains', 'php')->get();
// [['name' => 'Alice', ...]]

ArrayQuery::from($items)->where('tags', 'not_contains', 'php')->get();
// [['name' => 'Bob', ...]]
```

### Dot Notation for Nested Arrays

```php
$items = [
    ['name' => 'Alice', 'address' => ['city' => 'NYC']],
    ['name' => 'Bob', 'address' => ['city' => 'LA']],
];

ArrayQuery::from($items)
    ->where('address.city', '=', 'NYC')
    ->get();
```

## API

| Method | Description |
|---|---|
| `ArrayQuery::from(array $items)` | Create a new query from an array of associative arrays |
| `where(string $key, string $operator, mixed $value)` | Filter by comparison (`=`, `==`, `===`, `!=`, `<>`, `>`, `<`, `>=`, `<=`, `like`, `not like`, `contains`, `not_contains`) |
| `whereIn(string $key, array $values)` | Filter where value is in list |
| `whereNotNull(string $key)` | Filter where value is not null |
| `whereNull(string $key)` | Filter where value is null |
| `whereBetween(string $key, mixed $min, mixed $max)` | Filter where value is between min and max (inclusive) |
| `sort(string $key, string $direction = 'asc')` | Sort results by key (`asc` or `desc`) |
| `limit(int $count)` | Limit number of results |
| `offset(int $count)` | Skip a number of results |
| `select(array $keys)` | Select only specified keys |
| `pluck(string $key)` | Extract a single column as a flat array |
| `groupBy(string $key)` | Group results by a key |
| `distinct(?string $key = null)` | Remove duplicate items, optionally by key |
| `chunk(int $size)` | Split results into arrays of the given size |
| `map(callable $fn)` | Transform items with a callback |
| `first()` | Get the first result or `null` |
| `last()` | Get the last result or `null` |
| `count()` | Count the results |
| `get()` | Get all results |
| `sum(string $key)` | Sum values of a key |
| `avg(string $key)` | Average values of a key |
| `min(string $key)` | Minimum value of a key |
| `max(string $key)` | Maximum value of a key |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## Support

If you find this project useful:

⭐ [Star the repo](https://github.com/philiprehberger/php-array-query)

🐛 [Report issues](https://github.com/philiprehberger/php-array-query/issues?q=is%3Aissue+is%3Aopen+label%3Abug)

💡 [Suggest features](https://github.com/philiprehberger/php-array-query/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)

❤️ [Sponsor development](https://github.com/sponsors/philiprehberger)

🌐 [All Open Source Projects](https://philiprehberger.com/open-source-packages)

💻 [GitHub Profile](https://github.com/philiprehberger)

🔗 [LinkedIn Profile](https://www.linkedin.com/in/philiprehberger)

## License

[MIT](LICENSE)
