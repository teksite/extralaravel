<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Teksite\Extralaravel\Support\Digits;

class CodeMeliRule implements ValidationRule
{

    private const int LENGTH = 10;

    /**
     * Validate an Iranian national code (Code Melli).
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_int($value)) {
            $fail('validation.string')->translate();

            return;
        }

        if (!$this->isValidNationalCode($this->normalize($value))) {
            $fail($this->invalidMessage())->translate();
        }


    }

    /**
     * Prefer the application's own "validation.invalid_national_code" translation when it exists.
     */
    private function invalidMessage(): string
    {
        return trans()->has('validation.invalid_national_code')
            ? 'validation.invalid_national_code'
            : 'The :attribute must be a valid Iranian national code.';
    }

    private function normalize(string|int $value): string
    {
        if (is_int($value)) return str_pad((string) $value, self::LENGTH, '0', STR_PAD_LEFT);

        return trim(Digits::toEnglish($value));
    }

    private function isValidNationalCode(string $code): bool
    {
        if (preg_match('/^\d{10}$/', $code) !== 1) return false;

        // Codes made of a single repeated digit pass the checksum but are never issued.
        if (preg_match('/^(\d)\1{9}$/', $code) === 1)  return false;


        $sum = 0;

        for ($index = 0; $index < 9; $index++) {
            $sum += (int) $code[$index] * (10 - $index);
        }

        $remainder = $sum % 11;
        $expectedCheckDigit = $remainder < 2 ? $remainder : 11 - $remainder;

        return (int) $code[9] === $expectedCheckDigit;
    }
}
