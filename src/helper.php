<?php

use Illuminate\Support\Str;

if (!function_exists('changeToSlug')) {
    /**
     * @param string $slug
     * @param string $separator
     * @return \Illuminate\Support\Stringable
     */
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
    /**
     * @param string $string
     * @return string
     */
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
    /**
     * convert second to hour-minute-second
     * format parameter can be array to return arrays of values carbon format
     * @param int|null $seconds
     * @param string|null $format
     * @return string|array
     */
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
    /**
     * @return string
     */
    function currentUrlWithoutQueries(): string
    {
        return env('APP_URL') . parse_url(url()->current(), PHP_URL_PATH);
    }
}


if (!function_exists('exploding')) {
    /**
     * @param string|null $string
     * @return \Illuminate\Support\Collection|null
     */
    function exploding(?string $string): ?\Illuminate\Support\Collection
    {
        if ($string === null) return null;
        return collect(explode(',', $string))
            ->flatMap(fn($item) => explode('،', trim($item)))
            ->map(fn($item) => trim($item));
    }
}

if (!function_exists('var_export_short')) {
    /**
     * @param $expression
     * @param bool $return
     * @return array|string|string[]|void|null
     */
    function var_export_short($expression, bool $return = false)
    {

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

if (!function_exists('arrayToDot')) {
    /**
     * @param string $name
     * @return string|null
     */
    function arrayToDot(string $name): ?string
    {
        $dottedName= str_contains($name, '[')
            ? preg_replace(['/\[/', '/\]/'], ['.' ,''], $name)
            : $name;
        return trim($dottedName ,'.');

    }
}

if (!function_exists('dotToArray')) {
    /**
     * @param string $name
     * @return string|null
     */
    function dotToArray(string $name): ?string
    {
        return str_contains($name, '.')
            ? preg_replace('/\./', '][', $name) . ']'
            : $name;
    }
}

if (!function_exists('normalizePath')) {
    /**
     * @param string $path
     * @return string
     */
    function normalizePath(string $path): string
    {
        // Replace all "/" and "\" with DIRECTORY_SEPARATOR
        $normalizedPath = str_replace(['/', '\\', '/\\', '\\/'], DIRECTORY_SEPARATOR, $path);

        // Ensure the path ends with DIRECTORY_SEPARATOR
        return rtrim($normalizedPath, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Determines if a given language uses right-to-left (RTL) script.
     *
     * @param string|null $language Language code to check (optional)
     * @return bool True if the language is RTL, false otherwise
     */
    function is_rtl(?string $language = null): bool
    {
        $rtlLanguages = [
            'ar' => 'Arabic',   // Arabic - عربی
            'fa' => 'Persian',  // Persian - فارسی
            'he' => 'Hebrew',   // Hebrew - عبری
            'ur' => 'Urdu',     // Urdu - اردو
            'ps' => 'Pashto',   // Pashto - پشتو
            'ku' => 'Kurdish',  // Kurdish - کردی
            'sd' => 'Sindhi',   // Sindhi - سندی
            'yi' => 'Yiddish',  // Yiddish - ییدیش
        ];

        $languageCodes = array_keys($rtlLanguages);

        if ($language === null) {
            return in_array(app()->getLocale(), $languageCodes);
        }

        return in_array($language, $languageCodes);
    }
}


if (!function_exists('get_error')) {

    function get_error($errors ,string $name)
    {
        $stringifiedName=arrayToDot($name);
        return $errors->get($stringifiedName);
    }
}

if (!function_exists('getStaticData')) {
    /**
     * Retrieve data from a specified path using dot notation, similar to Laravel's config().
     *
     * @param string $key Dot notation key (e.g., 'app.data.times.iran.tehran')
     * @param mixed|null $default Default value if key or file is not found
     * @param string $pre_path Optional prefix path to prepend to the key
     * @return mixed
     */
    function getStaticData(string $key, mixed $default = null, string $pre_path = ''): mixed
    {

        // Normalize pre_path to dot notation
        $pre_path = trim(str_replace(['/', '\\'], '.', $pre_path), '.');
        $notedPath = $pre_path ? $pre_path . '.' . $key : $key;

        // Generate cache key
        $cacheKey = 'data_' . str_replace('.', '_', $notedPath);

        // Use cache to avoid repeated file loading
        return cache()->remember($cacheKey, now()->addHour(), function () use ($notedPath, $default) {
            // Split key into segments
            $segments = explode('.', trim($notedPath, '.'));

            // Prevent empty key
            if (empty($segments)) {
                return $default;
            }

            // Find the file path
            $fileSegments = [];
            $arrayKeys = [];
            $filePath = '';

            foreach ($segments as $index => $segment) {
                $fileSegments[] = $segment;
                $potentialPath = base_path(implode(DIRECTORY_SEPARATOR, $fileSegments) . '.php');

                if (file_exists($potentialPath)) {
                    $filePath = $potentialPath;
                    $arrayKeys = array_slice($segments, $index + 1);
                    break;
                }
            }

            // Return default if no valid file is found
            if (!$filePath) {
                return $default;
            }

            // Load the data file
            $data = include $filePath;

            // Navigate through nested array
            foreach ($arrayKeys as $key) {
                if (is_array($data) && array_key_exists($key, $data)) {
                    $data = $data[$key];
                } else {
                    return $default;
                }
            }

            return $data;
        });
    }
}
