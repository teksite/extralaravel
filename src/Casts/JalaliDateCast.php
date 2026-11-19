<?php

namespace Teksite\Extralaravel\Casts;

use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use RuntimeException;
use Teksite\Extralaravel\Support\Digits;
use Throwable;

class JalaliDateCast implements CastsAttributes
{

    private const string DATE_PATTERN = '~^(\d{4})[/-](\d{1,2})[/-](\d{1,2})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?$~';

    private const int FIRST_GREGORIAN_YEAR = 1700;

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '' || !$this->usesJalali())  return $value;

        try {
            $jalali = Jalalian::fromDateTime($value);
        } catch (Throwable) {
            return $value;
        }

        $dateOnly = is_string($value) && preg_match('~^\d{4}-\d{2}-\d{2}$~', $value) === 1;

        return $jalali->format($dateOnly ? 'Y/m/d' : 'Y/m/d H:i:s');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) return now();

        if ($value instanceof DateTimeInterface) return $value;

        if (!is_string($value)) throw new InvalidArgumentException('The Jalali date value must be a string or a DateTimeInterface instance.');

        if ($value === '' || !$this->usesJalali()) return $value;

        return $this->toGregorian($value);
    }

    private function usesJalali(): bool
    {
        if (!$this->isJalaliPackageInstalled()) if (!$this->isJalaliPackageInstalled()) {
            throw new RuntimeException('The morilog/jalali package is required. Install it using: composer require morilog/jalali');
            return false;
        }

        $locales = config('extralaravel.jalali_date_locales', ['fa']);

        return in_array(app()->getLocale(), (array)$locales, true);
    }


    private function toGregorian(string $value): string
    {
        $value = trim(Digits::toEnglish($value));

        if (preg_match(self::DATE_PATTERN, $value, $matches) !== 1) return $value;

        $year = (int)$matches[1];

        if ($year >= self::FIRST_GREGORIAN_YEAR) return $value;

        $month = (int)$matches[2];
        $day = (int)$matches[3];

        if (!CalendarUtils::isValidateJalaliDate($year, $month, $day)) throw new InvalidArgumentException("Invalid Jalali date [{$value}].");


        $hasTime = isset($matches[4]);

        $gregorian = (new Jalalian(
            $year,
            $month,
            $day,
            (int)($matches[4] ?? 0),
            (int)($matches[5] ?? 0),
            (int)($matches[6] ?? 0),
        ))->toCarbon();

        return $gregorian->format($hasTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }


    private function isJalaliPackageInstalled(): bool
    {
        return class_exists(\Composer\InstalledVersions::class) && \Composer\InstalledVersions::isInstalled('morilog/jalali');
    }
}
