<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class LogicMakeCommand extends GeneratorCommand
{

    protected $name = 'make:logic';

    protected $description = 'Create a new logic class (for repository pattern) to use DB query and other logical process';

    protected function getStub()
    {
        return __DIR__.'/../stubs/logic-class.stub';
    }


    protected function getPath($name)
    {
        $baseDir =  base_path('app/Logics');

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

        $module = $this->option('module');
        $namespace =  $this->rootNamespace() . 'Logics';
        return $namespace . '\\' . str_replace('/', '\\', $name);
    }


    protected function getOptions()
    {
        return [
//            ['module', 'M', InputOption::VALUE_OPTIONAL, 'The module to create the request in.'],
        ];
    }
}
