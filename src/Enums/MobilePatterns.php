<?php

namespace Teksite\Extralaravel\Enums;

use Illuminate\Support\Str;
use Teksite\Extralaravel\Support\Digits;

enum MobilePatterns
{
    case IRAN;
    case USA;
    case CANADA;
    case UNITED_KINGDOM;
    case GERMANY;
    case FRANCE;
    case TURKEY;
    case UAE;
    case INDIA;
    case AUSTRALIA;

    public function code(): string
    {
        return match ($this) {
            self::IRAN           => '98',
            self::USA            => '1',
            self::CANADA         => '1',
            self::UNITED_KINGDOM => '44',
            self::GERMANY        => '49',
            self::FRANCE         => '33',
            self::TURKEY         => '90',
            self::UAE            => '971',
            self::INDIA          => '91',
            self::AUSTRALIA      => '61',
        };
    }

    public function pattern(): string
    {
        return match ($this) {
            self::IRAN           => '/^(\+98|0098|0)?9\d{9}$/',
            self::USA            => '/^(\+1)?[2-9]\d{9}$/',
            self::CANADA         => '/^(\+1)?[2-9]\d{9}$/',
            self::UNITED_KINGDOM => '/^(\+44|0)?7\d{9}$/',
            self::GERMANY        => '/^(\+49|0)?1[5-7]\d{8,10}$/',
            self::FRANCE         => '/^(\+33|0)?[67]\d{8}$/',
            self::TURKEY         => '/^(\+90|0)?5\d{9}$/',
            self::UAE            => '/^(\+971|0)?5\d{8}$/',
            self::INDIA          => '/^(\+91|0)?[6-9]\d{9}$/',
            self::AUSTRALIA      => '/^(\+61|0)?4\d{8}$/',
        };
    }

    /**
     * Normalize the raw input and test it against this country's pattern.
     */
    public function validate(string $phone): bool
    {
        return $this->matches(self::normalize($phone));
    }

    /**
     * Test an already normalized number against this country's pattern.
     *
     * Use this when checking several countries so the input is normalized only once.
     */
    public function matches(string $normalizedPhone): bool
    {
        return preg_match($this->pattern(), $normalizedPhone) === 1;
    }

    /**
     * Convert digits to English, drop every character except digits and "+", and turn a leading "00" into "+".
     */
    public static function normalize(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', Digits::toEnglish(trim($phone))) ?? '';

        if (str_starts_with($phone, '00')) return '+' . substr($phone, 2);

        return $phone;
    }


    public static function country(string $country): ?self
    {
        $country = strtoupper(str_replace([' ', '-'], '_', trim($country)));

        foreach (self::cases() as $case) {
            if ($case->name === $country)return $case;
        }

        return null;
    }

    public static function validateWithCountry(string $phone, string|self $country): bool
    {
        $country = is_string($country) ? self::country($country) : $country;

        return $country?->validate($phone) ?? false;
    }

    /**
     * Detect the first country whose pattern matches the number.
     *
     * Numbers without an international prefix can match several countries, and the USA and Canada
     * share one pattern, so the first matching case (declaration order) wins.
     */
    public static function detectCountry(string $phone): ?self
    {
        $phone = self::normalize($phone);

        foreach (self::cases() as $country) {
            if ($country->matches($phone)) {
                return $country;
            }
        }

        return null;
    }

    /**
     * Convert a number to its digits-only international form (e.g. "09121234567" => "989121234567").
     *
     * Numbers that already carry an international prefix are validated and returned without the "+".
     * National numbers require $country to know which prefix to add.
     */
    public static function canonical(string $phone, string|self|null $country = null): ?string
    {
        $phone = self::normalize($phone);

        if (str_starts_with($phone, '+'))  return self::detectCountry($phone) === null ? null : substr($phone, 1);

        $country = is_string($country) ? self::country($country) : $country;

        if ($country === null || !$country->matches($phone)) return null;


        return $country->code() . ltrim($phone, '0');
    }

    /**
     * Country name.
     */
    public function label(): string
    {
        return match ($this) {
            self::IRAN           => 'Iran',
            self::USA            => 'United States',
            self::CANADA         => 'Canada',
            self::UNITED_KINGDOM => 'United Kingdom',
            self::GERMANY        => 'Germany',
            self::FRANCE         => 'France',
            self::TURKEY         => 'Turkey',
            self::UAE            => 'United Arab Emirates',
            self::INDIA          => 'India',
            self::AUSTRALIA      => 'Australia',
        };
    }
}
