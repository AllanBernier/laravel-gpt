<?php

namespace AllanBernier\LaravelGpt;

use AllanBernier\LaravelGpt\Console\MakeChatToolCommand;
use Illuminate\Support\ServiceProvider;

class LaravelGptServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../config/laravel-gpt.php' => config_path('laravel-gpt.php'),
        ], 'laravel-gpt-config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeChatToolCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-gpt.php',
            'laravel-gpt'
        );
    }
}
