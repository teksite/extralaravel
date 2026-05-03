<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class SoftDeleteControllerMakeCommand extends GeneratorCommand
{

    protected $name = 'make:controller-trash';

    protected $description = 'Create a new controller for soft deleted entities.';

    protected function getStub(): string
    {
        return __DIR__ . '/../stubs/' . ($this->option('api')
                ? 'trash-resource-controller-api.stub'
                :'trash-resource-controller.stub');
    }


    protected function getPath($name)
    {
        $baseDir = base_path('app/Http/Controllers');

        $relativePath = Str::replaceFirst($baseDir, '', $name);
        $absolutePath = base_path($relativePath);
        return str_replace('\\', '/', $absolutePath) . '.php';
    }

    /**
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $namespace = $this->rootNamespace() . 'Http\\Controllers';
        return $namespace . '\\' . str_replace('/', '\\', $name);
    }


    protected function getOptions(): array
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'api trash resource controller.'],
        ];
    }
}
