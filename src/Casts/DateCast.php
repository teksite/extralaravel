<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class DateCast implements CastsAttributes
{
    private string $lang = 'fa';

    public function __construct()
    {
        $this->lang = App::getLocale();
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($this->lang === 'fa') return dateToJalali($value);
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($this->lang === 'fa' && $value) return dateToGregorian($value);
        return $value ?? now();

    }
}
