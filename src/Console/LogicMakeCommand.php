<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Teksite\Extralaravel\Console\Concerns\ResolvesStubs;

class LogicMakeCommand extends GeneratorCommand
{

    use ResolvesStubs;

    protected $name = 'make:logic';

    protected $description = 'Create a reusable class for encapsulating application logic and handling processing operations across different parts of the application';

    protected $type = 'Logic';

    protected function getStub(): string
    {
        return $this->resolveStubPath('logic-class.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Logics';
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'],
        ];
    }

}
