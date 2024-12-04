<?php

namespace Alexkramse\LaravelTranslatableTable;

use Illuminate\Support\ServiceProvider;

class TranslatableTableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/table-translations.php', 'table-translations'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/table-translations.php' => config_path('table-translations.php'),
        ], 'config');
    }
}
