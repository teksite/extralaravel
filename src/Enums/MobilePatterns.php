<?php

namespace Teksite\Extralaravel\Enums;

use Illuminate\Support\Str;

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

    public function validate(string $phone): bool
    {
        return (bool)preg_match($this->pattern(), self::normalize($phone));
    }

    /**
     * Normalize phone number
     */
    public static function normalize(string $phone): string
    {
        $phone = trim($phone);

        $phone = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $phone
        );

        $phone = str_replace(
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $phone
        );

        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        return $phone;
    }


    public static function country(string $country): ?self
    {
        $country = strtoupper(str_replace([' ', '-'], '_', trim($country)));

        foreach (self::cases() as $case) {
            if ($case->name === $country) {
                return $case;
            }
        }
        return null;
    }

    public static function validateWithCountry(string $phone, string|self $country): bool
    {
        $country = is_string($country) ? self::country($country) : $country;
        return $country?->validate($phone) ?? false;
    }

    public static function detectCountry(string $phone): ?self
    {
        $phone = self::normalize($phone);

        foreach (self::cases() as $country) {
            if ($country->validate($phone)) {
                return $country;
            }
        }

        return null;
    }

    public static function canonical(string $phone, string|self|null $country = null): ?string
    {
        $phone = self::normalize($phone);

        $country = is_string($country) ? self::country($country) : $country;

        if (str_starts_with($phone, '+')) {
            $detected = self::detectCountry($phone);
            if (!$detected) return null;
            return ltrim($phone, '+');
        }
        if (!$country) return null;

        if (!$country->validate($phone)) return null;

        $phone = ltrim($phone, '0');

        return $country->code() . $phone;
    }

    /**
     * Country Name
     */
    public function label(): string
    {
        return match ($this) {
            self::IRAN => 'Iran',
            self::USA => 'United States',
            self::CANADA => 'Canada',
            self::UNITED_KINGDOM => 'United Kingdom',
            self::GERMANY => 'Germany',
            self::FRANCE => 'France',
            self::TURKEY => 'Turkey',
            self::UAE => 'United Arab Emirates',
            self::INDIA => 'India',
            self::AUSTRALIA => 'Australia',
        };
    }
}
