<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Teksite\Extralaravel\Console\Concerns\ResolvesStubs;

class ServiceMakeCommand extends GeneratorCommand
{

    use ResolvesStubs;

    protected $name = 'make:service';

    protected $description = 'Create a reusable service class';

    protected $type = 'Service';

    protected function getStub(): string
    {
        return $this->resolveStubPath('service-class.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Services';
    }

    /**
     * @return array<int, array<int, mixed>>
     */

}
