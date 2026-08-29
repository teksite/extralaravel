<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class LogicMakeCommand extends GeneratorCommand
{

    protected $name = 'make:logic';

    protected $description = 'Create a new logic class (for repository pattern) to use DB query and other logical process';

    protected function getStub(): string
    {
        return __DIR__.'/../stubs/logic-class.stub';
    }


    protected function getPath($name): string
    {
        $baseDir =  base_path('app/Logics');

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

        $namespace =  $this->rootNamespace() . 'Logics';
        return $namespace . '\\' . str_replace('/', '\\', $name);
    }
}
