<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class IpCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : (string) $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        $ip = $value ?? request()->ip();

        if ($ip === null) return null;

        if (!is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) throw new InvalidArgumentException("The [{$key}] attribute must be a valid IPv4 or IPv6 address.");

        return $ip;
    }
}
