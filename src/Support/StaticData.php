<?php

namespace Teksite\Extralaravel\Support;

/**
 * Reads values from PHP data files using dot notation.
 *
 * Resolving "app.data.times.iran.tehran" looks for app/data/times/iran/tehran.php (or a shorter
 * prefix such as app/data/times.php) and then walks the remaining segments through the returned array.
 *
 * Security: the resolved files are executed with require, so lookups are restricted to the configured
 * data directory. Without this boundary a user-controlled key could execute any PHP file inside the
 * project (for example "bootstrap.app"). Symlinks pointing outside the directory are rejected.
 *
 * This class is registered as a scoped binding so loaded data never leaks between Octane requests.
 */
final class StaticData
{
    /**
     * Resolved file and remaining array keys, indexed by the full dotted path.
     *
     * @var array<string, array{0: string, 1: list<string>}|null>
     */
    private array $resolved = [];

    /**
     * Loaded file contents, indexed by real file path.
     *
     * @var array<string, array<array-key, mixed>|null>
     */
    private array $loaded = [];

    private string|false|null $root = null;

    public function __construct(private readonly string $basePath, private readonly string $dataPath = 'app/data',) {}

    public function get(string $key, mixed $default = null, string $prePath = ''): mixed
    {
        $segments = $this->segments($key, $prePath);

        if ($segments === null)  return $default;


        $location = $this->locate($segments);

        if ($location === null) return $default;

        [$file, $nestedKeys] = $location;

        $data = $this->load($file);

        if ($data === null) return $default;

        foreach ($nestedKeys as $nestedKey) {
            if (!is_array($data) || !array_key_exists($nestedKey, $data))  return $default;

            $data = $data[$nestedKey];
        }
        return $data;
    }

    /**
     * @return list<string>|null
     */
    private function segments(string $key, string $prePath): ?array
    {
        $prePath = trim(str_replace(['/', '\\'], '.', $prePath), '.');
        $key = trim($key, '.');

        if ($key === '') return null;

        $segments = explode('.', $prePath !== '' ? $prePath . '.' . $key : $key);

        foreach ($segments as $segment) {

            if ($segment === '' || str_contains($segment, "\0") || str_contains($segment, '/') || str_contains($segment, '\\')) {
                return null;
            }
        }

        return $segments;
    }

    /**
     * @param list<string> $segments
     * @return array{0: string, 1: list<string>}|null
     */
    private function locate(array $segments): ?array
    {
        $rootParts = $this->rootParts();
        $root = $this->root();

        if ($rootParts === null || $root === false)return null;


        $rootDepth = count($rootParts);

        if (count($segments) <= $rootDepth || array_slice($segments, 0, $rootDepth) !== $rootParts) {
            return null;
        }

        $cacheKey = implode('.', $segments);

        if (array_key_exists($cacheKey, $this->resolved)) return $this->resolved[$cacheKey];


        $this->resolved[$cacheKey] = null;

        for ($length = $rootDepth + 1, $total = count($segments); $length <= $total; $length++) {
            $candidate = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR
                . implode(DIRECTORY_SEPARATOR, array_slice($segments, 0, $length)) . '.php';

            $realPath = realpath($candidate);

            if ($realPath === false || !is_file($realPath))  continue;

            // Rejects symlinks that escape the data directory.
            if (!str_starts_with($realPath, $root . DIRECTORY_SEPARATOR)) {
                continue;
            }

            return $this->resolved[$cacheKey] = [$realPath, array_slice($segments, $length)];
        }

        return null;
    }

    /**
     * @return list<string>|null
     */
    private function rootParts(): ?array
    {
        $parts = [];

        foreach (explode('/', str_replace('\\', '/', $this->dataPath)) as $part) {

            if ($part === '' || $part === '.')continue;

            if ($part === '..')  return null;

            $parts[] = $part;
        }

        return $parts === [] ? null : $parts;
    }

    private function root(): string|false
    {
        if ($this->root !== null)return $this->root;


        $rootParts = $this->rootParts();

        if ($rootParts === null) return $this->root = false;

        $real = realpath(rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $rootParts));

        return $this->root = ($real !== false && is_dir($real)) ? rtrim($real, '/\\') : false;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private function load(string $file): ?array
    {
        if (!array_key_exists($file, $this->loaded)) {
            $data = (static fn (string $path): mixed => require $path)($file);

            $this->loaded[$file] = is_array($data) ? $data : null;
        }

        return $this->loaded[$file];
    }
}
