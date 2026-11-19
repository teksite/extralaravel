<?php

namespace Teksite\Extralaravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Teksite\Extralaravel\Console\Concerns\ResolvesStubs;

class ApiRequestMakeCommand extends GeneratorCommand
{
    use ResolvesStubs;

    protected $name = 'make:request-api';

    protected $description = 'Create a new API request class in the request directory.';

    protected $type = 'Request';

    protected function getStub(): string
    {
        return $this->resolveStubPath('api-request.stub');
    }

    /**
     * Path and namespace resolution is left to GeneratorCommand. The previous custom getPath() built
     * paths from the class namespace ("App/Http/..."), which created a wrong-cased directory on
     * case-sensitive file systems.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Http\\Requests';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'],
        ];
    }
}
