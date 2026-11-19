<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Teksite\Extralaravel\Console\Concerns\ResolvesStubs;

class SoftDeleteControllerMakeCommand extends GeneratorCommand
{
    use ResolvesStubs;

    protected $name = 'make:controller-trash';

    protected $description = 'Create a new controller for soft deleted entities.';

    protected $type = 'Controller';

    protected function getStub(): string
    {
        return $this->resolveStubPath(
            $this->option('api') ? 'trash-resource-controller-api.stub' : 'trash-resource-controller.stub',
        );
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Http\\Controllers';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function getOptions(): array
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Generate a JSON (API) controller'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'],
        ];
    }
}
