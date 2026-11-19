<?php

namespace Teksite\Extralaravel\Support;

use Generator;
use LogicException;

/**
 * Walks arbitrarily nested iterables and yields only the leaf (non-iterable) values.
 *
 * Used by the @recursive Blade directive so the directive body runs in the template's
 * own scope instead of requiring a separate view or closure.
 */
final class NestedLeaves
{
    private const int MAX_DEPTH = 64;

    /**
     * @return Generator<int, mixed>
     */
    public static function of(mixed $items, int $remainingDepth = self::MAX_DEPTH): Generator
    {
        if (!is_iterable($items)) return;

        foreach ($items as $item) {
            if (!is_iterable($item)) {
                yield $item;
                continue;
            }

            // Guards against self-referencing Traversable objects.
            if ($remainingDepth <= 0) throw new LogicException('Maximum nesting depth exceeded while iterating nested data.');


            yield from self::of($item, $remainingDepth - 1);
        }
    }

    private function __construct() {}
}
