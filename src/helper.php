<?php

/*
 * Convert string to slug
 */

use Illuminate\Support\Str;

if (!function_exists('changeToSlug')) {
    function changeToSlug(string $slug, string $separator = '-'): \Illuminate\Support\Stringable
    {
        $slug = preg_replace_callback('/[A-Z]/', function ($matches) {
            return ' ' . strtolower($matches[0]);
        }, $slug);
        $slug = preg_replace('/[áàảạãăắằẳẵặâấầẩẫậ]/', 'a', $slug);
        $slug = preg_replace('/[éèẻẽẹêếềểễệ]/', 'e', $slug);
        $slug = preg_replace('/[iíìỉĩị]/', 'i', $slug);
        $slug = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/', 'o', $slug);
        $slug = preg_replace('/[úùủũụưứừửữự]/', 'u', $slug);
        $slug = preg_replace('/[ýỳỷỹỵ]/', 'y', $slug);
        $slug = preg_replace('/đ/', 'd', $slug);

        return Str::of($slug)->slug('-', null);
    }

}
if (!function_exists('toEnglishNumber')) {
    function toEnglishNumber(string $string): string
    {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabicDigits = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
        $allPersianDigits = array_merge($persianDigits, $arabicDigits);
        $replaces = [...range(0, 9), ...range(0, 9)];

        return str_replace($allPersianDigits, $replaces, $string);
    }
}
if (!function_exists('convertSeconds')) {
    function convertSeconds(?int $seconds = null, ?string $format = 'string'): string|array
    {
        if ($seconds === null) return "00:00:00";

        $hours = str_pad((string)floor($seconds / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string)floor(($seconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $sec = str_pad((string)($seconds % 60), 2, '0', STR_PAD_LEFT);

        if ($format === 'array') {
            return [
                'hours' => (int)$hours,
                'minutes' => (int)$minutes,
                'seconds' => (int)$sec,
            ];
        }

        return "{$hours}:{$minutes}:{$sec}";
    }
}
if (!function_exists('currentUrlWithoutQueries')) {
    function currentUrlWithoutQueries(): string
    {
        return env('APP_URL') . parse_url(url()->current(), PHP_URL_PATH);
    }
}


if (!function_exists('exploding')) {
    function exploding(?string $string): ?\Illuminate\Support\Collection
    {
        if ($string === null) return null;
        return collect(explode(',', $string))
            ->flatMap(fn($item) => explode('،', trim($item)))
            ->map(fn($item) => trim($item));
    }
}

if (!function_exists('exploding')) {
    function exploding(?string $string): ?\Illuminate\Support\Collection
    {
        if ($string === null) return null;
        return collect(explode(',', $string))
            ->flatMap(fn($item) => explode('،', trim($item)))
            ->map(fn($item) => trim($item));
    }
}

if (!function_exists('var_export_short')) {
    function var_export_short($expression,bool $return=false) {

        $export = var_export($expression, true);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);

        if ((bool)$return) return $export; else echo $export;
    }
}

