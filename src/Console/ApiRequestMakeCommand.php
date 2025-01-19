<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ApiRequestMakeCommand extends GeneratorCommand
{

    protected $name = 'make:request-api';

    protected $description = 'Create a new API request class in the request directory.';

    protected function getStub()
    {
        return __DIR__.'/../stubs/api-request.stub';
    }


    protected function getPath($name)
    {
        $baseDir = base_path('app/Http/Requests');

        $relativePath = Str::replaceFirst($baseDir, '', $name);
        $absolutePath=base_path($relativePath);
        return str_replace('\\', '/', $absolutePath) . '.php';
    }

    /**
     * تنظیمات نام‌گذاری کلاس.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');
        $namespace = $this->rootNamespace() . 'Http\\Requests';
        return $namespace . '\\' . str_replace('/', '\\', $name);
    }


    protected function getOptions()
    {
        return [
//            ['module', 'M', InputOption::VALUE_OPTIONAL, 'The module to create the request in.'],
        ];
    }
}
