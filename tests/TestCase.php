<?php

namespace Teksite\Extralaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Teksite\Extralaravel\ExtraLaravelServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [ExtraLaravelServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }
}
