<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Teksite\Extralaravel\Support\Slug;

class SlugCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * @throws \Exception
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) return null;

        if (!is_string($value))  throw new InvalidArgumentException('The slug value must be a string.');

        return Slug::make($value)->toString();
    }
}
