<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MobileRule implements ValidationRule
{
    private string $country ;
    public function __construct(string $country = 'iran')
    {
        $this->country = strtolower($country);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern=$this->getPattern();
        if (!$pattern) $fail(__("the chosen country in not at the counties list, check it again or add it"));
            if (!preg_match($pattern, $value)) {
                $fail(__("the phone number is not matched with :country ", ['country' => $this->country]));
            }
    }

    private function getPattern(): string
    {
        return config('mobile-pattern')[$this->country];

    }




}
