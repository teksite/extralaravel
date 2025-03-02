<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SlugCast implements CastsAttributes
{
    protected $slugs=[];

    public function __construct(public ?string $routeName =null , ...$slugs)
    {
        $this->slugs =$slugs;
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (request()->ajax() && !is_null($this->routeName)){
            return  route($this->routeName, $value) ;
        }
        return  $value ;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return changeToSlug($value);
    }
}
