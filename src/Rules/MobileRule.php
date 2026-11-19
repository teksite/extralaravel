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
     * Longest accepted raw input. Real numbers are far shorter, so this only rejects abusive payloads
     * before any normalization work happens.
     */
    private const int MAX_INPUT_LENGTH = 32;

    /**
     * @var list<MobilePatterns>
     */
    private array $countries;

    public function __construct(MobilePatterns|array|string $countries = MobilePatterns::IRAN)
    {
        $this->countries = $this->prepareCountries($countries);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '' || strlen($value) > self::MAX_INPUT_LENGTH) {
            $fail($this->invalidMessage())->translate();

        }

        $normalized = MobilePatterns::normalize($value);


        foreach ($this->countries as $country) {
            if ($country->matches($normalized)) {
                return;
            }
        }

        $fail('The :attribute must be a valid mobile number for: :countries')->translate([
            'countries' => implode(', ', array_map(
                static fn(MobilePatterns $country): string => $country->label(),
                $this->countries,
            )),
        ]);

        $allowedCountries = implode(', ', array_map(fn(MobilePatterns $country) => $country->label(), $this->countries));

        $fail(__("The :attribute must be a valid mobile number for: :countries", ['attribute' => $attribute, 'countries' => $allowedCountries,]));
    }

    /**
     * Prefer the application's own "validation.mobile" translation when it exists.
     */
    private function invalidMessage(): string
    {
        return trans()->has('validation.mobile')
            ? 'validation.mobile'
            : 'The :attribute must be a valid mobile number.';
    }


    /**
     * @param MobilePatterns|string|array<int, MobilePatterns|string> $countries
     * @return list<MobilePatterns>
     */
    private function prepareCountries(MobilePatterns|array|string $countries): array
    {
        $result = [];

        foreach (is_array($countries) ? $countries : [$countries] as $country) {
            if (is_string($country)) {
                $resolved = MobilePatterns::country($country)
                    ?? throw new InvalidArgumentException("Unknown country [{$country}].");
            } elseif ($country instanceof MobilePatterns) {
                $resolved = $country;
            } else {
                throw new InvalidArgumentException('Invalid country type.');
            }

            // Keyed by case name so duplicates collapse without comparing enum objects.
            $result[$resolved->name] = $resolved;
        }

        return array_values($result);
    }
}
