<?php

namespace Teksite\Extralaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;


class ImageCast implements CastsAttributes
{
    #TODO generate this Cast for images

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;

//        if (request()->routeIs('admin.*') || !str_starts_with($value, '/')) return $value;
//
//        $path = public_path($value);
//
//        if (File::exists($path . '.webp')) return $value . '.webp';
//
//        $manager = new ImageManager(Driver::class);
//        $image = $manager->read(public_path($value));
//
//        $encoded = $image->encode(new WebpEncoder(quality: 80));
//
//        $encoded->save($path. '.webp') ;

//        return  $value . '.webp';

    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
