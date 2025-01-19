<?php

namespace Teksite\Extralaravel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Validator;
use Teksite\Extralaravel\Console\ApiRequestMakeCommand;
use Teksite\Extralaravel\Console\SoftDeleteControllerMakeCommand;

class ExtraLaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
       $this->registerConfig();
    }


    public function boot(): void
    {
        $this->bootCommands();;
        $this->publish();
        $this->bootTrashResource();
        $this->bootTranslations();

    }
    public function registerConfig(): void
    {
        $mobilePatternPath = config_path('mobile-pattern.php'); // Path to the published file
        $passwordPatternPath = config_path('password-pattern.php'); // Path to the published file

        $this->mergeConfigFrom(
            file_exists($mobilePatternPath) ? $mobilePatternPath : __DIR__ . '/config/mobile-pattern.php', 'mobile-pattern');
        $this->mergeConfigFrom(
            file_exists($passwordPatternPath) ? $passwordPatternPath : __DIR__ . '/config/password-pattern.php', 'password-pattern');
}
    public function bootCommands(): void
    {
        $this->commands([
            ApiRequestMakeCommand::class,
            SoftDeleteControllerMakeCommand::class
        ]);
    }
    public function publish(): void
    {
        $this->publishes([
            __DIR__ . '/config/password-pattern.php' => config_path('password-pattern.php')
        ], 'password-pattern');

    }
    public function bootTrashResource(): void
    {
        Route::macro('trashResource', function ($name, $controller, $options = []) {
            $actions = [
                'index'   => ['get', '/trash', 'index'],          // Show trashed items
                'reinstate'    => ['patch', '/trash/{id}', 'reinstate'],    // Restore a specific item
                'remove'   => ['delete', '/trash/{id}', 'remove'],  // Permanently delete a specific item
                'restore' => ['patch', '/trash', 'restore'],      // Restore all trashed items
                'purge'   => ['delete', '/trash', 'purge'],       // Permanently delete all trashed items
            ];

            $prefix = $options['prefix'] ?? '';
            $middleware = $options['middleware'] ?? [];
            $namePrefix = $options['as'] ?? $name;

            // Group routes with the provided options
            Route::group(compact('prefix', 'middleware'), function () use ($name, $controller, $actions, $namePrefix) {
                foreach ($actions as $key => $action) {
                    Route::{$action['method']}(
                        "$name{$action['uri']}",
                        [$controller, $action['action']]
                    )->name("$namePrefix.trash.$key");
                }
            });
        });
    }
    public function bootTranslations(): void
    {
        $langPath = __DIR__.'/lang/';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'extra');
            $this->loadJsonTranslationsFrom($langPath);
        }
    }
}
