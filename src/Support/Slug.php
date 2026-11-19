<?php

declare(strict_types=1);

namespace Teksite\Extralaravel\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

final class Slug
{
    /**
     * Splits camelCase and PascalCase words ("MyPost" => "My Post", "HTMLParser" => "HTML Parser").
     */
    private const string WORD_BOUNDARY_PATTERN = '/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/';

    /**
     * Vietnamese letters with diacritics, grouped by their ASCII base letter.
     */
    private const array DIACRITIC_LETTERS = [
        'a' => 'áàảạãăắằẳẵặâấầẩẫậ',
        'e' => 'éèẻẽẹêếềểễệ',
        'i' => 'íìỉĩị',
        'o' => 'óòỏõọôốồổỗộơớờởỡợ',
        'u' => 'úùủũụưứừửữự',
        'y' => 'ýỳỷỹỵ',
        'd' => 'đ',
    ];

    public static function make(string $text, string $separator = '-'): Stringable
    {
        return Str::of(self::normalize($text))->slug($separator, null);
    }

    /**
     * Prepare text for slugging: split camelCase words, lowercase, and strip Vietnamese diacritics.
     *
     * The replacement is multibyte-safe. Applying a non-unicode regex character class to these
     * letters would match single bytes and corrupt other UTF-8 text such as Persian.
     */
    public static function normalize(string $text): string
    {
        $text = preg_replace(self::WORD_BOUNDARY_PATTERN, ' ', $text) ?? $text;

        return strtr(mb_strtolower($text, 'UTF-8'), self::diacriticMap());
    }

    /**
     * @return array<string, string>
     */
    private static function diacriticMap(): array
    {
        static $map = null;

        if ($map !== null)  return $map;

        $map = [];

        foreach (self::DIACRITIC_LETTERS as $base => $letters) {
            foreach (mb_str_split($letters) as $letter) {
                $map[$letter] = $base;
            }
        }

        return $map;
    }
}
