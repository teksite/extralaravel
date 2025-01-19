<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRule implements ValidationRule
{


    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message=[];
        $validation=true;
        if (!preg_match('/(?=.*?[a-z])/', $value)) {
            $validation=false;
            $message[]=(__("the password should contain at least one lowercase character"));
        }
        if (!preg_match('/(?=.*?[A-Z])/', $value)) {
            $validation=false;
            $message[]=(__("the password should contain at least one uppercase character"));
        }
        if (!preg_match('/(?=.*?[0-9])/', $value)) {
            $validation=false;
            $message[]=(__("the password should contain at least one number"));
        }
        if (!preg_match('/(?=.*?[#?!@$ %^&*-])/', $value)) {
            $validation=false;
            $message[]=(__("the password should contain at least one special characters (#?!@$ %^&*-)"));
        }

        if (!preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$ %^&*-]).{8,}$/', $value)) {
            $validation=false;
            $message[]=$fail(__("the password in not strong enough"));
        }
        if ($validation ===false) $fail(implode(", \n", $message));

    }




}
