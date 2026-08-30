<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRule implements ValidationRule
{


    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail(__('validation.string', ['attribute' => $attribute]));
            return;
        }

        $message = [];

        if (strlen($value) < 6) {
            $message[] = __("the password should at least 6 characters");
        }
        if (!preg_match('/(?=.*?[a-z])/', $value)) {
            $message[] = __("the password should contain at least one lowercase character");
        }
        if (!preg_match('/(?=.*?[A-Z])/', $value)) {
            $message[] = __("the password should contain at least one uppercase character");
        }
        if (!preg_match('/(?=.*?[0-9])/', $value)) {
            $message[] = __("the password should contain at least one number");
        }
        if (!preg_match('/(?=.*?[#?!@$ %^&*-])/', $value)) {
            $message[] = __("the password should contain at least one special characters (#?!@$ %^&*-)");
        }

        if (count($message) > 0) $fail(implode(", \n", $message));

    }


}
