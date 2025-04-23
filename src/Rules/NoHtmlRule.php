<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoHtmlRule implements ValidationRule
{


    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strip_tags($value) != $value) {
            $fail(__('The :attribute field cannot contain HTML tags' ,['attribute' => $attribute]));
        }
    }



}
