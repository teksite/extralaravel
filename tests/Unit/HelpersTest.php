<?php

namespace Teksite\Extralaravel\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function test_convert_seconds(): void
    {
        $this->assertSame('01:02:05', convertSeconds(3725));
        $this->assertSame('00:00:00', convertSeconds(-5));
        $this->assertSame(['hours' => 0, 'minutes' => 0, 'seconds' => 0], convertSeconds(null, 'array'));
        $this->assertSame(['hours' => 1, 'minutes' => 2, 'seconds' => 5], convertSeconds(3725, 'array'));
    }

    public function test_array_dot_conversion(): void
    {
        $this->assertSame('user.address.city', arrayToDot('user[address][city]'));
        $this->assertSame('tags', arrayToDot('tags[]'));
        $this->assertSame('user[address][city]', dotToArray('user.address.city'));
        $this->assertSame('name', dotToArray('name'));
    }

    public function test_normalize_path_collapses_separators_and_keeps_the_root(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->assertSame("a{$ds}b{$ds}c", normalizePath('a//b\\c/'));
        $this->assertSame($ds, normalizePath('/'));
        $this->assertSame($ds, normalizePath('///'));
        $this->assertSame('', normalizePath(''));
    }

    public function test_to_english_number(): void
    {
        $this->assertSame('09', toEnglishNumber('۰۹'));
    }

    public function test_normalize_persian_text_handles_null(): void
    {
        $this->assertNull(normalizePersianText(null));
    }
}
