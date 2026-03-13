<?php

declare(strict_types=1);

namespace PhilipRehberger\ArrayQuery;

use InvalidArgumentException;

final class ArrayQuery
{
    /** @var array<int, array<string, mixed>> */
    private array $items;

    /** @var array<callable> */
    private array $filters = [];

    private ?string $sortKey = null;

    private string $sortDirection = 'asc';

    private ?int $limitCount = null;

    private ?int $offsetCount = null;

    /** @var array<string>|null */
    private ?array $selectKeys = null;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function from(array $items): self
    {
        return new self($items);
    }

    public function where(string $key, string $operator, mixed $value): self
    {
        $this->filters[] = function (array $item) use ($key, $operator, $value): bool {
            $itemValue = $this->resolveKey($item, $key);

            return match ($operator) {
                '=', '==' => $itemValue == $value,
                '===' => $itemValue === $value,
                '!=', '<>' => $itemValue != $value,
                '>' => $itemValue > $value,
                '<' => $itemValue < $value,
                '>=' => $itemValue >= $value,
                '<=' => $itemValue <= $value,
                'like' => is_string($itemValue) && is_string($value) && str_contains(strtolower($itemValue), strtolower(str_replace('%', '', $value))),
                'not like' => is_string($itemValue) && is_string($value) && ! str_contains(strtolower($itemValue), strtolower(str_replace('%', '', $value))),
                default => throw new InvalidArgumentException("Unsupported operator: '{$operator}'."),
            };
        };

        return $this;
    }

    /**
     * @param  array<mixed>  $values
     */
    public function whereIn(string $key, array $values): self
    {
        $this->filters[] = fn (array $item): bool => in_array($this->resolveKey($item, $key), $values, false);

        return $this;
    }

    public function whereNotNull(string $key): self
    {
        $this->filters[] = fn (array $item): bool => $this->resolveKey($item, $key) !== null;

        return $this;
    }

    public function whereNull(string $key): self
    {
        $this->filters[] = fn (array $item): bool => $this->resolveKey($item, $key) === null;

        return $this;
    }

    public function whereBetween(string $key, mixed $min, mixed $max): self
    {
        $this->filters[] = function (array $item) use ($key, $min, $max): bool {
            $value = $this->resolveKey($item, $key);

            return $value >= $min && $value <= $max;
        };

        return $this;
    }

    public function sort(string $key, string $direction = 'asc'): self
    {
        $this->sortKey = $key;
        $this->sortDirection = strtolower($direction);

        return $this;
    }

    public function limit(int $count): self
    {
        $this->limitCount = $count;

        return $this;
    }

    public function offset(int $count): self
    {
        $this->offsetCount = $count;

        return $this;
    }

    /**
     * @param  array<string>  $keys
     */
    public function select(array $keys): self
    {
        $this->selectKeys = $keys;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function pluck(string $key): array
    {
        return array_map(
            fn (array $item): mixed => $this->resolveKey($item, $key),
            $this->execute(),
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function groupBy(string $key): array
    {
        $groups = [];
        foreach ($this->execute() as $item) {
            $groupKey = (string) ($this->resolveKey($item, $key) ?? '');
            $groups[$groupKey][] = $item;
        }

        return $groups;
    }

    public function map(callable $fn): self
    {
        $this->items = array_map($fn, $this->items);

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $results = $this->execute();

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function last(): ?array
    {
        $results = $this->execute();

        return ! empty($results) ? end($results) : null;
    }

    public function count(): int
    {
        return count($this->execute());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        return $this->execute();
    }

    public function sum(string $key): int|float
    {
        return array_sum($this->pluck($key));
    }

    public function avg(string $key): float
    {
        $values = $this->pluck($key);
        if (empty($values)) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }

    public function min(string $key): mixed
    {
        $values = $this->pluck($key);

        return ! empty($values) ? min($values) : null;
    }

    public function max(string $key): mixed
    {
        $values = $this->pluck($key);

        return ! empty($values) ? max($values) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function execute(): array
    {
        $items = $this->items;

        // Apply filters
        foreach ($this->filters as $filter) {
            $items = array_filter($items, $filter);
        }
        $items = array_values($items);

        // Apply sort
        if ($this->sortKey !== null) {
            $key = $this->sortKey;
            $direction = $this->sortDirection;
            usort($items, function (array $a, array $b) use ($key, $direction): int {
                $aVal = $this->resolveKey($a, $key);
                $bVal = $this->resolveKey($b, $key);
                $result = $aVal <=> $bVal;

                return $direction === 'desc' ? -$result : $result;
            });
        }

        // Apply offset
        if ($this->offsetCount !== null) {
            $items = array_slice($items, $this->offsetCount);
        }

        // Apply limit
        if ($this->limitCount !== null) {
            $items = array_slice($items, 0, $this->limitCount);
        }

        // Apply select
        if ($this->selectKeys !== null) {
            $keys = $this->selectKeys;
            $items = array_map(function (array $item) use ($keys): array {
                $selected = [];
                foreach ($keys as $k) {
                    $selected[$k] = $this->resolveKey($item, $k);
                }

                return $selected;
            }, $items);
        }

        return $items;
    }

    /**
     * Resolve a dot-notation key in an array.
     *
     * @param  array<string, mixed>  $item
     */
    private function resolveKey(array $item, string $key): mixed
    {
        $segments = explode('.', $key);
        $current = $item;

        foreach ($segments as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
