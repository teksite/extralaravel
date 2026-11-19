<?php

use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Teksite\Extralaravel\Support\ArrayExporter;
use Teksite\Extralaravel\Support\Digits;
use Teksite\Extralaravel\Support\PersianText;
use Teksite\Extralaravel\Support\Slug;
use Teksite\Extralaravel\Support\StaticData;

if (!function_exists('changeToSlug')) {
    /**
     * Convert a string to a slug.
     */
    function changeToSlug(string $slug, string $separator = '-'): Stringable
    {
        return Slug::make($slug, $separator);
    }
}

if (!function_exists('toEnglishNumber')) {
    /**
     * Convert Arabic and Persian digits to English digits.
     */
    function toEnglishNumber(string $value): string
    {
        return Digits::toEnglish($value);
    }
}

if (!function_exists('normalizePersianText')) {
    /**
     * Convert Arabic letter variants to Persian and strip diacritics.
     */
    function normalizePersianText(?string $string): ?string
    {
        return $string === null ? null : PersianText::normalize($string);
    }
}



if (!function_exists('convertSeconds')) {
    /**
     * Convert seconds to "HH:MM:SS", or to an array of hours, minutes and seconds when $format is "array".
     * Negative values are treated as zero.
     *
     * @return string|array{hours: int, minutes: int, seconds: int}
     */
    function convertSeconds(?int $seconds = null, ?string $format = 'string'): string|array
    {
        $seconds = max(0, $seconds ?? 0);

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainder = $seconds % 60;

        if ($format === 'array') {
            return ['hours' => $hours, 'minutes' => $minutes, 'seconds' => $remainder];
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainder);
    }
}


if (!function_exists('currentUrlWithoutQueries')) {
    /**
     * Return the current URL without the query string.
     */
    function currentUrlWithoutQueries(): string
    {
        return url()->current();
    }
}


if (!function_exists('exploding')) {
    /**
     * Explode a string by the English "," and the Persian "،" commas, trimming every part.
     *
     * @param string|null $string
     * @return Collection|null
     */
    function exploding(?string $string): ?Collection
    {
        if ($string === null) return null;

        return collect(explode(',', str_replace('،', ',', $string)))
            ->map(static fn (string $item): string => trim($item));
    }
}

if (!function_exists('var_export_short')) {
    /**
     * Export a value as short-array-syntax PHP code.
     *
     * Returns the code when $return is true, otherwise prints it.
     */
    function var_export_short(mixed $expression, bool $return = false): ?string
    {
        $export = ArrayExporter::export($expression);

        if ($return) return $export;

        echo $export;

        return null;
    }
}

if (!function_exists('arrayToDot')) {
    /**
     * Convert array bracket syntax to dot syntax, e.g. "user[address][city]" => "user.address.city".
     */
    function arrayToDot(string $name): string
    {
        return trim(strtr($name, ['[' => '.', ']' => '']), '.');
    }
}

if (!function_exists('dotToArray')) {
    /**
     * Convert dot syntax to array bracket syntax (inverse of arrayToDot).
     *
     * e.g. "user.address.city" => "user[address][city]"
     */
    function dotToArray(string $name): string
    {
        if (!str_contains($name, '.'))  return $name;

        $segments = explode('.', $name);
        $first = array_shift($segments);

        return $first . '[' . implode('][', $segments) . ']';
    }
}

if (!function_exists('normalizePath')) {
    /**
     * Normalize directory separators and collapse repeated ones.
     *
     * This does not resolve "." or ".." segments, so it must not be used as a path traversal guard.
     */
    function normalizePath(string $path): string
    {
        $normalized = preg_replace('~[\\\\/]+~', DIRECTORY_SEPARATOR, $path) ?? $path;
        $trimmed = rtrim($normalized, DIRECTORY_SEPARATOR);

        // A path made only of separators is the filesystem root, not an empty string.
        return $trimmed === '' && $normalized !== '' ? DIRECTORY_SEPARATOR : $trimmed;
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Determine whether a locale uses a right-to-left script.
     *
     * Region and script suffixes are ignored, so "fa", "fa_IR" and "fa-IR" are all RTL.
     */
    function is_rtl(?string $language = null): bool
    {
        $language = strtolower(str_replace('-', '_', $language ?? app()->getLocale()));
        $primary = explode('_', $language, 2)[0];

        $rtlLocales = array_map('strtolower', (array) config('extralaravel.rtl_locales', []));

        return in_array($language, $rtlLocales, true) || in_array($primary, $rtlLocales, true);
    }
}



if (!function_exists('get_error')) {
    /**
     * Get validation errors for a field written in array bracket syntax, e.g. "users[0][email]".
     *
     * @return array<int, string>
     */
    function get_error(MessageBag $errors, string $name): array
    {
        return $errors->get(arrayToDot($name));
    }
}

if (!function_exists('getStaticData')) {
    /**
     * Read static data from PHP files inside the configured data directory using dot notation.
     *
     * Example: getStaticData('app.data.times.iran.tehran') with extralaravel.data_path = "app/data".
     */
    function getStaticData(string $key, mixed $default = null, string $prePath = ''): mixed
    {
        return app(StaticData::class)->get($key, $default, $prePath);
    }
}
