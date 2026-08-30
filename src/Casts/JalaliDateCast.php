<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class JalaliDateCast implements CastsAttributes
{



    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (app()->getLocale() === 'fa' && function_exists('dateToJalali')) return dateToJalali($value);
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (app()->getLocale() === 'fa' && $value && function_exists('dateToGregorian')) return dateToGregorian($value);
        return $value ?? now();

    }
}
