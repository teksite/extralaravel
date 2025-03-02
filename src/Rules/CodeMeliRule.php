<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CodeMeliRule implements ValidationRule
{


    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValidIranianNationalCode($value)) {
            $fail(__(':attribute in not entered correctly or is not match with national code of IRAN' ,['attribute' => $attribute]));
        }
    }

    private function isValidIranianNationalCode($code)
    {
        if (!preg_match('/^\d{10}$/', $code)) return false;

        $digits = str_split($code);
        $check = (int) array_pop($digits);
        $sum = 0;

        foreach ($digits as $i => $num) {
            $sum += $num * (10 - $i);
        }

        $remainder = $sum % 11;

        return ($remainder < 2 && $check == $remainder) || ($remainder >= 2 && $check == (11 - $remainder));
    }


}
