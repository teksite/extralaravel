<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;
use Teksite\Extralaravel\Enums\MobilePatterns;

class MobileRule implements ValidationRule
{
    /**
     * @var MobilePatterns[]
     */
    private array $countries;

    public function __construct(MobilePatterns|array|string $countries = MobilePatterns::IRAN)
    {
        $this->countries = $this->prepareCountries($countries);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '') {
            $fail(__('validation.mobile'));
            return;
        }

        foreach ($this->countries as $country) {
            if ($country->validate($value)) {
                return;
            }
        }

        $allowedCountries = implode(', ', array_map(fn(MobilePatterns $country) => $country->label(), $this->countries));

        $fail(__("The :attribute must be a valid mobile number for: :countries", ['attribute' => $attribute, 'countries' => $allowedCountries,]));
    }

    /**
     * @return MobilePatterns[]
     */
    private function prepareCountries(MobilePatterns|array|string $countries): array
    {
        if (!is_array($countries)) $countries = [$countries];
        
        $result = [];

        foreach ($countries as $country) {

            if ($country instanceof MobilePatterns) {
                $result[] = $country;
                continue;
            }

            if (!is_string($country)) {
                throw new InvalidArgumentException(
                    'Invalid country type.'
                );
            }

            $enum = MobilePatterns::country($country);

            if (!$enum) {
                throw new InvalidArgumentException(
                    "Unknown country [{$country}]"
                );
            }

            $result[] = $enum;
        }

        return array_values(
            array_unique($result, SORT_REGULAR)
        );
    }
}
