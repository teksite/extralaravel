<?php

namespace Teksite\Extralaravel\Support;

final class PersianText
{
    /**
     * Arabic harakat, tanwin, shadda and sukun.
     */
    private const string DIACRITICS_PATTERN = '/[\x{064B}-\x{0652}]/u';

    /**
     * Arabic letter variants mapped to their Persian counterparts.
     */
    private const array LETTERS = [
        "\u{0623}" => "\u{0627}", // Alef with hamza above -> Alef
        "\u{0625}" => "\u{0627}", // Alef with hamza below -> Alef
        "\u{0649}" => "\u{06CC}", // Alef maksura -> Persian Yeh
        "\u{064A}" => "\u{06CC}", // Arabic Yeh -> Persian Yeh
        "\u{0626}" => "\u{06CC}", // Yeh with hamza above -> Persian Yeh
        "\u{0643}" => "\u{06A9}", // Arabic Kaf -> Persian Keheh
        "\u{0629}" => "\u{0647}", // Teh marbuta -> Heh
        "\u{0624}" => "\u{0648}", // Waw with hamza above -> Waw
    ];

    /**
     * Normalize Arabic letter variants and strip diacritics.
     */
    public static function normalize(string $text): string
    {
        $withoutDiacritics = preg_replace(self::DIACRITICS_PATTERN, '', $text);

        return strtr($withoutDiacritics ?? $text, self::LETTERS);
    }

}
