<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ApiRequestMakeCommand extends GeneratorCommand
{

    protected $name = 'make:request-api';

    protected $description = 'Create a new API request class in the request directory.';

    protected function getStub(): string
    {
        return __DIR__.'/../stubs/api-request.stub';
    }


    protected function getPath($name): string
    {
        $baseDir = base_path('app/Http/Requests');

        $relativePath = Str::replaceFirst($baseDir, '', $name);
        $absolutePath=base_path($relativePath);
        return str_replace('\\', '/', $absolutePath) . '.php';
    }


    /**
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name): string
    {
        $name = ltrim($name, '\\/');
        $namespace = $this->rootNamespace() . 'Http\\Requests';
        return $namespace . '\\' . str_replace('/', '\\', $name);
    }
}
