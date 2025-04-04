<?php

namespace Teksite\Extralaravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Teksite\Extralaravel\Console\ApiRequestMakeCommand;
use Teksite\Extralaravel\Console\LogicMakeCommand;
use Teksite\Extralaravel\Console\SoftDeleteControllerMakeCommand;

class ExtraLaravelServiceProvider extends ServiceProvider
{

    private const TRASH_ACTIONS = [
        'index' => ['method' => 'get', 'uri' => '/trash', 'action' => 'index'],
        'reinstate' => ['method' => 'patch', 'uri' => '/trash/{id}', 'action' => 'reinstate'],
        'prune' => ['method' => 'delete', 'uri' => '/trash/{id}', 'action' => 'prune'],
        'restore' => ['method' => 'patch', 'uri' => '/trash', 'action' => 'restore'],
        'flush' => ['method' => 'delete', 'uri' => '/trash', 'action' => 'flush'],
    ];

    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(): void
    {
        $this->bootCommands();
        $this->publishConfigFiles();
        $this->registerTrashResourceMacro();
        $this->registerRecursiveBladeDirectives();
    }

    private function registerConfig(): void
    {
        foreach ($this->configFiles() as $key => $filename) {
            $configPath = config_path($filename);
            $defaultPath = __DIR__ . "/config/{$filename}";

            $this->mergeConfigFrom(
                file_exists($configPath) ? $configPath : $defaultPath,
                $key
            );
        }
    }

    private function bootCommands(): void
    {
        $this->commands([
            ApiRequestMakeCommand::class,
            SoftDeleteControllerMakeCommand::class,
            LogicMakeCommand::class,
        ]);
    }

    private function publishConfigFiles(): void
    {
        foreach ($this->configFiles() as $key => $filename) {
            $this->publishes([
                __DIR__ . "/config/{$filename}" => config_path($filename)
            ], $key);
        }
    }

    private function registerTrashResourceMacro(): void
    {
        $provider = $this; // Capture the ServiceProvider instance

        Route::macro('trashResource', function (string $name, string $controller, array $options = []) use ($provider) {
            $config = $provider->buildRouteConfiguration($name, $controller, $options);

            Route::group([
                'prefix' => $config['prefix'],
                'middleware' => $config['middleware'],
                'as' => $config['namePrefix'] . '.',
            ], function () use ($provider, $config) {
                $provider->registerTrashRoutes($config);
            });
        });
    }

    public function buildRouteConfiguration(string $name, string $controller, array $options): array
    {
        return [
            'name' => $name,
            'controller' => $controller,
            'prefix' => $options['prefix'] ?? '',
            'middleware' => $options['middleware'] ?? [],
            'namePrefix' => $options['as'] ?? $name,
            'only' => $options['only'] ?? array_keys(self::TRASH_ACTIONS),
            'except' => $options['except'] ?? [],
        ];
    }

    public function registerTrashRoutes(array $config): void
    {
        $allowedActions = $this->filterTrashActions($config['only'], $config['except']);

        foreach ($allowedActions as $key => $action) {
            Route::{$action['method']}(
                "{$config['name']}{$action['uri']}",
                [$config['controller'], $action['action']]
            )->name("trash.{$key}");
        }
    }

    public function filterTrashActions(array $only, array $except): array
    {
        $actions = self::TRASH_ACTIONS;

        if (!empty($only)) {
            return array_intersect_key($actions, array_flip($only));
        }

        if (!empty($except)) {
            return array_diff_key($actions, array_flip($except));
        }

        return $actions;
    }

    private function registerRecursiveBladeDirectives(): void
    {
        Blade::directive('recursive', function ($expression) {
            // جدا کردن آرایه و متغیر آیتم از expression
            [$array, $item] = array_map('trim', explode(',', str_replace(['(', ')'], '', $expression)));

            // اگر آرایه به صورت مستقیم پاس داده شده (مثل (array) $messages)
            if (str_contains($array, '(array)')) {
                $array = str_replace('(array)', '', $array);
                $arraySource = "(array) \${$array}";
            } else {
                $arraySource = "\${$array}";
            }

            return "<?php
                if (!isset({$arraySource})) {
                    echo 'Array is not defined';
                } elseif (is_array({$arraySource}) || {$arraySource} instanceof \\Traversable) {
                    foreach ({$arraySource} as \${$item}) {
                        if (is_array(\${$item}) || \${$item} instanceof \\Traversable) {
                            echo \$this->view->make('recursive', ['{$array}' => \${$item}, '{$item}' => '{$item}'])->render();
                        } else {
                            ?>";
        });

        Blade::directive('endrecursive', function () {
            return "<?php
                        }
                    }
                }
            ?>";
        });
    }

    public function configFiles(): array
    {

        $configPackagePath = __DIR__ . "/config/";
        $files = [];

        if (is_dir($configPackagePath)) {
            $filesArray = File::allFiles($configPackagePath);
            foreach ($filesArray as $item) {
                $filename = $item->getBasename();
                if (str_ends_with($filename, '.php')) {
                    $configName = str_replace('.php', '', $filename);
                    $files[$configName] = $filename;
                }
            }

        };
        return $files;

    }

}
