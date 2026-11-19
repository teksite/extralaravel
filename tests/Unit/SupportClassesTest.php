<?php

namespace Teksite\Extralaravel\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Teksite\Extralaravel\Support\ArrayExporter;
use Teksite\Extralaravel\Support\Digits;
use Teksite\Extralaravel\Support\NestedLeaves;
use Teksite\Extralaravel\Support\PersianText;
use Teksite\Extralaravel\Support\Slug;

final class SupportClassesTest extends TestCase
{
    public function test_digits_are_converted_to_english(): void
    {
        $this->assertSame('123456abc', Digits::toEnglish('۱۲۳٤٥٦abc'));
    }

    public function test_arabic_letters_are_normalized_to_persian(): void
    {
        $this->assertSame("\u{06A9}\u{06CC}", PersianText::normalize("\u{0643}\u{064A}"));
        $this->assertSame('محمد', PersianText::normalize('مُحَمَّد'));
    }

    public function test_invalid_utf8_does_not_blank_the_text(): void
    {
        $this->assertNotSame('', PersianText::normalize("ab\xff"));
    }

    public function test_slug_normalization_keeps_persian_text_intact(): void
    {
        $this->assertSame('سلام دنیا', Slug::normalize('سلام دنیا'));
    }

    public function test_slug_normalization_splits_camel_case_without_shredding_acronyms(): void
    {
        $this->assertSame('my awesome post', Slug::normalize('MyAwesomePost'));
        $this->assertSame('html parser', Slug::normalize('HTMLParser'));
    }

    public function test_slug_normalization_strips_vietnamese_diacritics(): void
    {
        $this->assertSame('viet nam dep', Slug::normalize('Việt Nam Đẹp'));
    }

    public function test_array_exporter_round_trips_values_that_look_like_var_export_output(): void
    {
        $data = ['a' => 'my array (x)', 'list' => [1, 2.5, true, null], 'nested' => ['k' => "it's"], 'empty' => []];

        $this->assertSame($data, eval('return ' . ArrayExporter::export($data) . ';'));
    }

    public function test_array_exporter_omits_keys_for_lists(): void
    {
        $this->assertSame("[\n    1,\n    2,\n]", ArrayExporter::export([1, 2]));
    }

    public function test_nested_leaves_flattens_any_depth(): void
    {
        $this->assertSame([1, 2, 3, 4, 5], iterator_to_array(NestedLeaves::of([1, [2, [3, 4]], 5]), false));
        $this->assertSame([], iterator_to_array(NestedLeaves::of(null), false));
    }
}
