<?php

declare(strict_types=1);

namespace PhilipRehberger\ArrayQuery\Tests;

use PhilipRehberger\ArrayQuery\ArrayQuery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArrayQueryTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $users;

    protected function setUp(): void
    {
        $this->users = [
            ['name' => 'Alice', 'age' => 30, 'city' => 'NYC', 'score' => 85],
            ['name' => 'Bob', 'age' => 25, 'city' => 'LA', 'score' => 92],
            ['name' => 'Charlie', 'age' => 35, 'city' => 'NYC', 'score' => 78],
            ['name' => 'Diana', 'age' => 28, 'city' => 'Chicago', 'score' => null],
            ['name' => 'Eve', 'age' => 22, 'city' => 'LA', 'score' => 95],
        ];
    }

    #[Test]
    public function test_where_equals(): void
    {
        $results = ArrayQuery::from($this->users)
            ->where('city', '=', 'NYC')
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Alice', $results[0]['name']);
        $this->assertSame('Charlie', $results[1]['name']);
    }

    #[Test]
    public function test_where_greater_than(): void
    {
        $results = ArrayQuery::from($this->users)
            ->where('age', '>', 28)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Alice', $results[0]['name']);
        $this->assertSame('Charlie', $results[1]['name']);
    }

    #[Test]
    public function test_where_less_than(): void
    {
        $results = ArrayQuery::from($this->users)
            ->where('age', '<', 25)
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Eve', $results[0]['name']);
    }

    #[Test]
    public function test_where_not_equals(): void
    {
        $results = ArrayQuery::from($this->users)
            ->where('city', '!=', 'NYC')
            ->get();

        $this->assertCount(3, $results);
    }

    #[Test]
    public function test_where_like(): void
    {
        $results = ArrayQuery::from($this->users)
            ->where('name', 'like', '%ali%')
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results[0]['name']);
    }

    #[Test]
    public function test_where_in(): void
    {
        $results = ArrayQuery::from($this->users)
            ->whereIn('city', ['NYC', 'Chicago'])
            ->get();

        $this->assertCount(3, $results);
    }

    #[Test]
    public function test_where_not_null(): void
    {
        $results = ArrayQuery::from($this->users)
            ->whereNotNull('score')
            ->get();

        $this->assertCount(4, $results);
    }

    #[Test]
    public function test_where_null(): void
    {
        $results = ArrayQuery::from($this->users)
            ->whereNull('score')
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Diana', $results[0]['name']);
    }

    #[Test]
    public function test_where_between(): void
    {
        $results = ArrayQuery::from($this->users)
            ->whereBetween('age', 25, 30)
            ->get();

        $this->assertCount(3, $results);
    }

    #[Test]
    public function test_chained_filters(): void
    {
        $results = ArrayQuery::from($this->users)
            ->where('city', '=', 'LA')
            ->where('age', '>', 23)
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Bob', $results[0]['name']);
    }

    #[Test]
    public function test_sort_ascending(): void
    {
        $results = ArrayQuery::from($this->users)
            ->sort('age')
            ->get();

        $this->assertSame('Eve', $results[0]['name']);
        $this->assertSame('Charlie', $results[4]['name']);
    }

    #[Test]
    public function test_sort_descending(): void
    {
        $results = ArrayQuery::from($this->users)
            ->sort('age', 'desc')
            ->get();

        $this->assertSame('Charlie', $results[0]['name']);
        $this->assertSame('Eve', $results[4]['name']);
    }

    #[Test]
    public function test_limit(): void
    {
        $results = ArrayQuery::from($this->users)
            ->limit(2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Alice', $results[0]['name']);
        $this->assertSame('Bob', $results[1]['name']);
    }

    #[Test]
    public function test_offset_and_limit(): void
    {
        $results = ArrayQuery::from($this->users)
            ->offset(1)
            ->limit(2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('Bob', $results[0]['name']);
        $this->assertSame('Charlie', $results[1]['name']);
    }

    #[Test]
    public function test_select_keys(): void
    {
        $results = ArrayQuery::from($this->users)
            ->select(['name', 'age'])
            ->limit(1)
            ->get();

        $this->assertSame(['name' => 'Alice', 'age' => 30], $results[0]);
    }

    #[Test]
    public function test_pluck(): void
    {
        $names = ArrayQuery::from($this->users)
            ->pluck('name');

        $this->assertSame(['Alice', 'Bob', 'Charlie', 'Diana', 'Eve'], $names);
    }

    #[Test]
    public function test_group_by(): void
    {
        $groups = ArrayQuery::from($this->users)
            ->groupBy('city');

        $this->assertCount(3, $groups);
        $this->assertCount(2, $groups['NYC']);
        $this->assertCount(2, $groups['LA']);
        $this->assertCount(1, $groups['Chicago']);
    }

    #[Test]
    public function test_first_and_last(): void
    {
        $first = ArrayQuery::from($this->users)->first();
        $last = ArrayQuery::from($this->users)->last();

        $this->assertSame('Alice', $first['name']);
        $this->assertSame('Eve', $last['name']);
    }

    #[Test]
    public function test_count(): void
    {
        $count = ArrayQuery::from($this->users)
            ->where('city', '=', 'LA')
            ->count();

        $this->assertSame(2, $count);
    }

    #[Test]
    public function test_sum_avg_min_max(): void
    {
        $query = ArrayQuery::from($this->users)
            ->whereNotNull('score');

        $this->assertSame(350, $query->sum('score'));
        $this->assertSame(87.5, $query->avg('score'));
        $this->assertSame(78, $query->min('score'));
        $this->assertSame(95, $query->max('score'));
    }

    #[Test]
    public function test_first_on_empty_returns_null(): void
    {
        $result = ArrayQuery::from($this->users)
            ->where('city', '=', 'Berlin')
            ->first();

        $this->assertNull($result);
    }

    #[Test]
    public function test_dot_notation_nested_access(): void
    {
        $items = [
            ['name' => 'Alice', 'address' => ['city' => 'NYC', 'zip' => '10001']],
            ['name' => 'Bob', 'address' => ['city' => 'LA', 'zip' => '90001']],
        ];

        $results = ArrayQuery::from($items)
            ->where('address.city', '=', 'NYC')
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results[0]['name']);

        $zips = ArrayQuery::from($items)->pluck('address.zip');
        $this->assertSame(['10001', '90001'], $zips);
    }
}
