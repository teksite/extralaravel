<?php

namespace Teksite\Extralaravel;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Teksite\Extralaravel\Blades\HoneypotDirective;
use Teksite\Extralaravel\Blades\RecursiveDirectives;
use Teksite\Extralaravel\Console\ApiRequestMakeCommand;
use Teksite\Extralaravel\Console\PurgeCacheCommand;
use Teksite\Extralaravel\Console\LogicMakeCommand;
use Teksite\Extralaravel\Console\ServiceMakeCommand;
use Teksite\Extralaravel\Console\SoftDeleteControllerMakeCommand;
use Teksite\Extralaravel\Middleware\HoneypotMiddleware;
use Teksite\Extralaravel\Routing\Trash\PendingTrashResourceRegistration;
use Teksite\Extralaravel\Routing\Trash\TrashResourceRegistrar;
use Teksite\Extralaravel\Support\StaticData;

class ExtraLaravelServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->registerConfig();
        $this->registerSingletons();
        $this->registerScopes();
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/extralaravel.php', 'extralaravel');
    }

    private function registerSingletons(): void
    {
        $this->app->singleton(TrashResourceRegistrar::class);
    }

    private function registerScopes(): void
    {
        $this->app->scoped(StaticData::class, static fn(Application $app): StaticData => new StaticData(
            $app->basePath(),
            (string)$app->make('config')->get('extralaravel.data_path', 'app/data'),
        ));
    }


    public function boot(): void
    {
        $this->bootCommands();

        $this->registerTrashResourceMacro();

        $this->registerBladeDirectives();
        $this->registerMiddlewareAliases();


        $this->bootPublishingFiles();
    }

    private function bootPublishingFiles(): void
    {
        if (!$this->app->runningInConsole()) return;

            $this->publishes([
            __DIR__.'/config/extralaravel.php' => config_path('extralaravel.php'),
        ], ['extralaravel', 'extralaravel-config']);

        $this->publishes([
            __DIR__.'/stubs' => base_path('stubs/extralaravel'),
        ], ['extralaravel', 'extralaravel-stubs']);
    }


    private function bootCommands(): void
    {
        $command = [PurgeCacheCommand::class,];

        if ($this->app->runningInConsole()) {
            $command = array_merge(
                $command,
                [
                    ApiRequestMakeCommand::class,
                    SoftDeleteControllerMakeCommand::class,
                    LogicMakeCommand::class,
                    ServiceMakeCommand::class,

                ]);
        }
        $this->commands($command);
    }


    private function registerTrashResourceMacro(): void
    {
        Route::macro('trashResource', function (string $name, string $controller, array $options = []): PendingTrashResourceRegistration {
            return new PendingTrashResourceRegistration(
                app(TrashResourceRegistrar::class),
                $name,
                $controller,
                $options,
            );
        });

        Route::macro('trashResources', function (array $resources, array $options = []): void {
            foreach ($resources as $name => $controller) {
                Route::trashResource($name, $controller, $options);
            }
        });
    }

    private function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', static function (BladeCompiler $blade): void {
            RecursiveDirectives::register($blade);
            HoneypotDirective::register($blade);
        });
    }

    private function registerMiddlewareAliases(): void
    {
        $this->callAfterResolving('router', static function (Router $router): void {
            $router->aliasMiddleware('honeypot', HoneypotMiddleware::class);
        });
    }
}
