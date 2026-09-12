<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CodeMeliRule implements ValidationRule
{
    /**
     * Validate the given value.
     *
     * @param Closure(string): void $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_int($value)) {
            $fail(__('validation.string', ['attribute' => $attribute,]));
            return;
        }

        $code = $this->normalize($value);

        if ($code === '') {
            $fail(__('validation.required', ['attribute' => $attribute,]));
            return;
        }

        if (!$this->isValidIranianNationalCode($code)) {$fail(__('validation.invalid_national_code', ['attribute' => $attribute,]));

            return;
        }
    }

    /**
     * Normalize the national code.
     */
    private function normalize(string|int $value): string
    {
        $value = (string) $value;

        // Convert Arabic and Persian digits to English digits.
        $value = strtr($value, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',

            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);

        return trim($value);
    }

    /**
     * Determine whether the given value is a valid Iranian national code.
     */
    private function isValidIranianNationalCode(string $code): bool
    {
        if (!preg_match('/^\d{10}$/', $code)) return false;

        if (count(array_unique(str_split($code))) === 1) return false;

        $digits = str_split($code);

        $checkDigit = (int) array_pop($digits);

        $sum = 0;

        foreach ($digits as $index => $digit) {
            $sum += (int) $digit * (10 - $index);
        }

        $remainder = $sum % 11;

        $expectedCheckDigit = $remainder < 2
            ? $remainder
            : 11 - $remainder;

        return $checkDigit === $expectedCheckDigit;
    }
}
