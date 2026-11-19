<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;
use Teksite\Extralaravel\Tests\TestCase;

final class BladeDirectivesTest extends TestCase
{
    public function test_recursive_runs_the_body_for_every_leaf_at_any_depth(): void
    {
        $html = Blade::render('@recursive($items, $item){{ $item }},@endrecursive', [
            'items' => [1, [2, [3]], 4],
        ]);

        $this->assertSame('1,2,3,4,', $html);
    }

    public function test_recursive_accepts_collection_expressions_that_contain_commas(): void
    {
        $html = Blade::render('@recursive(array_merge($a, $b), $leaf){{ $leaf }};@endrecursive', [
            'a' => [1],
            'b' => [[2]],
        ]);

        $this->assertSame('1;2;', $html);
    }

    public function test_recursive_ignores_values_that_are_not_iterable(): void
    {
        $this->assertSame('', Blade::render('@recursive($items, $item){{ $item }}@endrecursive', ['items' => null]));
    }

    public function test_recursive_requires_a_collection_and_a_variable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Blade::compileString('@recursive($items)@endrecursive');
    }
}
