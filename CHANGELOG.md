# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-03-13

### Added

- Fluent query builder via `ArrayQuery::from()`.
- `where()` with operators: `=`, `==`, `===`, `!=`, `<>`, `>`, `<`, `>=`, `<=`, `like`, `not like`.
- `whereIn()`, `whereNotNull()`, `whereNull()`, `whereBetween()` filter methods.
- `sort()` with ascending and descending direction.
- `limit()` and `offset()` for pagination.
- `select()` to pick specific keys from results.
- `pluck()` to extract a single column.
- `groupBy()` to group results by a key.
- `map()` for custom transformations.
- `first()`, `last()`, `count()`, `get()` retrieval methods.
- `sum()`, `avg()`, `min()`, `max()` aggregate methods.
- Dot-notation support for nested array access.
