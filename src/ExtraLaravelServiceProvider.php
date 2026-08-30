<?php

namespace Teksite\Extralaravel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Teksite\Extralaravel\Blades\RecursiveDirectives;
use Teksite\Extralaravel\Console\ApiRequestMakeCommand;
use Teksite\Extralaravel\Console\PurgeCacheCommand;
use Teksite\Extralaravel\Console\LogicMakeCommand;
use Teksite\Extralaravel\Console\SoftDeleteControllerMakeCommand;
use Teksite\Extralaravel\Console\StorageCustomLink;
use Teksite\Extralaravel\Routing\Trash\TrashResourceOptions;
use Teksite\Extralaravel\Routing\Trash\TrashResourceRegistrar;

class ExtraLaravelServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->registerConfig();
        $this->app->singleton(TrashResourceRegistrar::class);
    }

    private function registerConfig(): void
    {
        $configPath = config_path('extralaravel.php');
        $this->mergeConfigFrom(file_exists($configPath) ? $configPath : __DIR__ . '/config/extralaravel.php', 'extralaravel');
    }

    public function boot(): void
    {
        $this->bootCommands();
        $this->publishConfigFiles();
        $this->registerTrashResourceMacro();
        $this->registerRecursiveBladeDirectives();
    }


    private function bootCommands(): void
    {
        $this->commands([
            ApiRequestMakeCommand::class,
            SoftDeleteControllerMakeCommand::class,
            LogicMakeCommand::class,
            PurgeCacheCommand::class,
            StorageCustomLink::class,
        ]);
    }

    private function publishConfigFiles(): void
    {
        $this->publishes([
            __DIR__ . '/config/extralaravel.php' => config_path('extralaravel.php'),
        ], ['extralaravel', 'extralaravel-config']);

    }

    private function registerTrashResourceMacro(): void
    {
        Route::macro('trashResource', function (string $name, string $controller, array $options = []): void {
            $registrar = app(TrashResourceRegistrar::class);
            $registrar->register($name, $controller, TrashResourceOptions::from($options));
        });
    }

    private function registerRecursiveBladeDirectives(): void
    {
        RecursiveDirectives::recursive();
    }
}
