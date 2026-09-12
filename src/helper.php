<?php

use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

if (!function_exists('changeToSlug')) {
    /**
     * convert string to slug
     */
    function changeToSlug(string $slug, string $separator = '-',): \Illuminate\Support\Stringable
    {
        $slug = preg_replace_callback('/[A-Z]/', function ($matches,) {
            return ' '.strtolower($matches[0]);
        }, $slug);
        $slug = preg_replace('/[áàảạãăắằẳẵặâấầẩẫậ]/', 'a', $slug);
        $slug = preg_replace('/[éèẻẽẹêếềểễệ]/', 'e', $slug);
        $slug = preg_replace('/[iíìỉĩị]/', 'i', $slug);
        $slug = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/', 'o', $slug);
        $slug = preg_replace('/[úùủũụưứừửữự]/', 'u', $slug);
        $slug = preg_replace('/[ýỳỷỹỵ]/', 'y', $slug);
        $slug = preg_replace('/đ/', 'd', $slug);

        return Str::of($slug)->slug($separator, null);
    }
}

if (!function_exists('toEnglishNumber')) {
    /**
     * Convert arabic and persian numbers to english numbers
     */
    function toEnglishNumber(string $value,): string
    {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',

            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }
}

if (!function_exists('normalizePersianText')) {
    /**
     * Convert arabic letters to persian
     */
    function normalizePersianText(?string $string,): null|string
    {
        if ($string === null) return null;
        $arabic_to_persian_map = [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'آ', 'ا' => 'ا',
            'ى' => 'ی', 'ي' => 'ی', 'ی' => 'ی',
            'ة' => 'ه',
            'ؤ' => 'و', 'ئ' => 'ی', 'ء' => 'ء',
            'َ' => '', 'ً' => '', 'ُ' => '', 'ٌ' => '', 'ِ' => '', 'ٍ' => '', 'ْ' => '', 'ّ' => '',
        ];
        $string = preg_replace('/[\x{064B}-\x{0652}]/u', '', $string);
        return strtr($string, $arabic_to_persian_map);
    }
}

if (!function_exists('convertSeconds')) {
    /**
     * convert second to hour-minute-second
     * format parameter can be array to return arrays of values carbon format
     */
    function convertSeconds(?int $seconds = null, ?string $format = 'string',): string|array
    {
        if ($seconds === null) return "00:00:00";

        $hours = str_pad((string)floor($seconds / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string)floor(($seconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $sec = str_pad((string)($seconds % 60), 2, '0', STR_PAD_LEFT);

        if ($format === 'array') {
            return [
                'hours'   => (int)$hours,
                'minutes' => (int)$minutes,
                'seconds' => (int)$sec,
            ];
        }

        return "{$hours}:{$minutes}:{$sec}";
    }
}

if (!function_exists('currentUrlWithoutQueries')) {
    /**
     * return current url without any query
     */
    function currentUrlWithoutQueries(): string
    {
        return url()->current();
    }
}

if (!function_exists('exploding')) {
    /**
     * explode string by "," and "،"
     */
    function exploding(?string $string,): ?\Illuminate\Support\Collection
    {
        if ($string === null) return null;
        return collect(explode(',', $string))
            ->flatMap(fn($item,) => explode('،', trim($item)))
            ->map(fn($item,) => trim($item));
    }
}

if (!function_exists('var_export_short')) {
    /**
     * @param      $expression
     * @param bool $return
     * @return array|string|string[]|void|null
     */
    function var_export_short($expression, bool $return = false,)
    {

        $export = var_export($expression, true);
        $patterns = [
            "/array \(/"                       => '[',
            "/^([ ]*)\)(,?)$/m"                => '$1]$2',
            "/=>[ ]?\n[ ]+\[/"                 => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);

        if ((bool)$return) return $export; else echo $export;
    }
}

if (!function_exists('arrayToDot')) {
    /**
     * convert array to dot syntax
     */
    function arrayToDot(string $name,): ?string
    {
        $dottedName = str_contains($name, '[')
            ? preg_replace(['/\[/', '/\]/'], ['.', ''], $name)
            : $name;
        return trim($dottedName, '.');

    }
}

if (!function_exists('dotToArray')) {
    /**
     * convert dotted string to array bracket syntax (inverse of arrayToDot)
     *
     * e.g. "user.address.city" => "user[address][city]"
     */
    function dotToArray(string $name,): ?string
    {
        if (!str_contains($name, '.')) return $name;

        $segments = explode('.', $name);
        $first = array_shift($segments);

        return $first.'['.implode('][', $segments).']';
    }
}

if (!function_exists('normalizePath')) {
    /**
     * normalize path directory
     */
    function normalizePath(string $path,): string
    {
        $normalizedPath = str_replace(['/', '\\', '/\\', '\\/'], DIRECTORY_SEPARATOR, $path);

        return rtrim($normalizedPath, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Determines if a given language uses right-to-left (RTL) script.
     */
    function is_rtl(?string $language = null,): bool
    {
        $language ??= app()->getLocale();

        return in_array(
            $language,
            config('extralaravel.rtl_locales' ,[]),
            true,
        );

    }
}

if (!function_exists('get_error')) {

    function get_error(MessageBag $errors, string $name,): array
    {
        return $errors->get(arrayToDot($name));
    }
}

if (!function_exists('getStaticData')) {
    /**
     * Get static data from PHP files using dot notation.
     *
     * Example:
     * getStaticData('app.data.times.iran.tehran');
     *
     * @param string $key
     * @param mixed  $default
     * @param string $prePath
     * @return mixed
     */
    function getStaticData(string $key, mixed $default = null, string $prePath = '',): mixed
    {
        $prePath = trim(str_replace(['/', '\\'], '.', $prePath), '.');

        $key = trim($key, '.');

        if ($key === '') return $default;

        $path = $prePath !== '' ? "{$prePath}.{$key}" : $key;

        $segments = explode('.', $path);

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_contains($segment, "\0") || str_contains($segment, '/') || str_contains($segment, '\\')) {
                return $default;
            }
        }

        // Cache resolved files and loaded data during the current request.
        static $resolvedFiles = [];
        static $loadedFiles = [];

        /*
         * Find the PHP file.
         *
         * Example:
         * app.data.times.iran.tehran
         *
         * Tries:
         * app.php
         * app/data.php
         * app/data/times.php
         * ...
         */
        if (!isset($resolvedFiles[$path])) {
            $resolvedFiles[$path] = null;

            $basePath = realpath(base_path());

            if ($basePath === false) return $default;

            $fileSegments = [];

            foreach ($segments as $index => $segment) {
                $fileSegments[] = $segment;

                $candidate = base_path(implode(DIRECTORY_SEPARATOR, $fileSegments).'.php');

                $realPath = realpath($candidate);

                if ($realPath === false || !is_file($realPath)) {
                    continue;
                }

                /*
                 * Make sure the resolved file is inside the application.
                 * This also protects against malicious symlinks.
                 */
                $base = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR);

                $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $realPath);

                if ($file !== $base && !str_starts_with($file, $base.DIRECTORY_SEPARATOR)) {
                    return $default;
                }

                $resolvedFiles[$path] = [$realPath, array_slice($segments, $index + 1),];

                break;
            }
        }

        if ($resolvedFiles[$path] === null) {
            return $default;
        }

        [$filePath, $arrayKeys] = $resolvedFiles[$path];

        /*
         * Load each PHP file only once per request.
         */
        if (!array_key_exists($filePath, $loadedFiles)) {
            $data = require $filePath;

            if (!is_array($data)) {
                return $default;
            }

            $loadedFiles[$filePath] = $data;
        }

        $data = $loadedFiles[$filePath];

        /*
         * Traverse nested array.
         */
        foreach ($arrayKeys as $arrayKey) {
            if (!is_array($data) || !array_key_exists($arrayKey, $data)) {
                return $default;
            }
            $data = $data[$arrayKey];
        }

        return $data;
    }
}
