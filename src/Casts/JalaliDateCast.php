<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class JalaliDateCast implements CastsAttributes
{



    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (in_array(app()->getLocale() ,config('extralaravel.jalalit_date_locals',[])) && function_exists('dateToJalali')) return dateToJalali($value);
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (in_array(app()->getLocale() ,config('extralaravel.jalalit_date_locals',[])) && $value && function_exists('dateToGregorian')) return dateToGregorian($value);
        return $value ?? now();

    }
}
