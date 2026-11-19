<?php

namespace Teksite\Extralaravel\Console\Concerns;

/**
 * Lets generator commands use a project-level stub when one has been published.
 *
 * Publish the package stubs with: php artisan vendor:publish --tag=extralaravel-stubs
 */
trait ResolvesStubs
{
    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath('stubs/extralaravel/' . $stub);

        return file_exists($customPath) ? $customPath : dirname(__DIR__, 2) . '/stubs/' . $stub;
    }
}
