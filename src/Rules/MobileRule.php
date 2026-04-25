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
    private array $patterns = [];

    /**
     * @param MobilePatterns|MobilePatterns[] $country
     */
    public function __construct(private readonly MobilePatterns|array $country = MobilePatterns::iran)
    {
        $this->patterns = $this->preparePatterns($this->$country);
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern->value, $value)) {
                return;
            }
        }

        $countryNames = implode(', ', array_map(fn($pattern) => $pattern->name, $this->patterns));
        $fail(__('the :attribute is note matched with :countries patterns ', ['attribute' => $attribute, 'countries' => $countryNames]));
    }


    /**
     * @param MobilePatterns|MobilePatterns[]|string[]|string $countryInput
     * @return MobilePatterns[]
     */
    private function preparePatterns(MobilePatterns|array|string $countryInput): array
    {
        $patterns = [];
        $countries = is_string($countryInput) ? [$countryInput] : (is_array($countryInput) ? $countryInput : [$countryInput]);

        foreach ($countries as $item) {
            $pattern = $item instanceof MobilePatterns ? $item : MobilePatterns::tryFrom($item);

            if ($pattern === null) {
                throw new InvalidArgumentException(trans(':attribute can not be recognized or is not matched with MobilePatterns enum', ['attribute' => $item]));
            }

            $patterns[] = $pattern;
        }
        $filteredPatterns = array_values(array_unique($patterns, SORT_REGULAR));
        if (empty($filteredPatterns)) {
            throw new InvalidArgumentException(trans('the patterns are not valid'));
        }
        return $filteredPatterns;
    }


}
