<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class MobileRule implements ValidationRule
{
    private array|string $country;

    public function __construct(string|array $country = 'iran')
    {
        $this->country = $country;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $patterns = $this->getPatterns()['patterns'];
        $countries = $this->getPatterns()['countries'];

        if (count($patterns) < 1 && count($countries)) abort(505, 'error in validation of countries');
        $validation = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $validation = true;
                break;
            }
        }
        if (!$validation) {
            $fail(__("the phone number is not matched with :country ", ['country' => implode(", ", $countries)]));
        }
    }

    private function getPatterns()
    {
        $countriesToValidate = is_string($this->country) ? [$this->country] : $this->country;
        $patterns = [];
        foreach ($countriesToValidate as $country) {
            $patterns['patterns'][] = config('mobile-pattern')[strtolower($country)];
            $patterns['countries'][] = strtoupper($country);
        }
        return $patterns;

    }


}
