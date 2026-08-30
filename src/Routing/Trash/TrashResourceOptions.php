<?php

namespace Teksite\Extralaravel\Routing\Trash;

use InvalidArgumentException;

final readonly class TrashResourceOptions
{
    /**
     * @param array<int, string> $middleware
     * @param array<int, string>|null $only
     * @param array<int, string> $except
     * @param array<string, string> $where
     * @param array<string, string> $parameters
     * @param array<string, string> $names
     */
    private function __construct(
        public string $prefix,
        public array  $middleware,
        public string $namePrefix,
        public ?array $only,
        public array  $except,
        public array  $where,
        public array  $parameters,
        public array  $names,
        public bool   $scopeBindings,
    ) {}

    public static function from(array $options): self
    {
        $only = self::normalizeActions($options['only'] ?? null);
        $except = self::normalizeActions($options['except'] ?? []);

        self::validateActionConflict($only, $except);

        return new self(
            self::normalizePrefix($options['prefix'] ?? ''),
            self::normalizeMiddleware($options['middleware'] ?? []),
            self::normalizeNamePrefix($options['as'] ?? ''),
            $only,
            $except,
            $options['where'] ?? [],
            $options['parameters'] ?? [],
            $options['names'] ?? [],
            (bool)($options['scopeBindings'] ?? false),
        );
    }

    private static function normalizePrefix(mixed $prefix): string
    {
        if (!is_string($prefix)) throw new InvalidArgumentException('Trash resource prefix must be a string.',);

        return trim($prefix, '/');
    }

    private static function normalizeNamePrefix(mixed $name): string
    {
        if (!is_string($name)) throw new InvalidArgumentException('Trash resource route name must be a string.',);
        return trim($name, '.');
    }

    private static function normalizeMiddleware(mixed $middleware): array
    {
        if (is_string($middleware)) return [$middleware];

        if (!is_array($middleware)) throw new InvalidArgumentException('Trash resource middleware must be a string or array.',);

        return array_values($middleware);
    }

    /**
     * @return array<int, string>|null
     */
    private static function normalizeActions(mixed $actions): ?array
    {
        if ($actions === null) return null;

        if (!is_array($actions)) throw new InvalidArgumentException('Trash resource actions must be an array.',);

        return array_values(array_unique(array_map(
            static fn(mixed $action): string => (string)$action,
            $actions,
        )));
    }

    private static function validateActionConflict(?array $only, array $except): void
    {
        if ($only === null || $except === []) return;
        $conflicts = array_intersect($only, $except);
        if ($conflicts === []) return;

        throw new InvalidArgumentException(sprintf('Trash resource actions cannot exist in both only and except: %s.', implode(', ', $conflicts)),);
    }
}
