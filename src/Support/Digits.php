<?php

namespace Teksite\Extralaravel\Support;

/**
 * Digit conversion shared by the helpers, rules and enums.
 *
 * The map lives in one constant so every consumer converts digits
 * the same way and the lookup table is never rebuilt per call.
 */
final class Digits
{
    private const array TO_ENGLISH = [
        // Persian (Extended Arabic-Indic) digits.
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        // Arabic-Indic digits.
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * Convert Persian and Arabic-Indic digits to English digits.
     */
    public static function toEnglish(string $value): string
    {
        return strtr($value, self::TO_ENGLISH);
    }
}
