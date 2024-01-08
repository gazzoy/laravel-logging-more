<?php

namespace Gazzoy\LaravelLoggingMore\Providers;

use Gazzoy\LaravelLoggingMore\Processors\LoggingMoreUidProcessor;
use Illuminate\Support\ServiceProvider;

class LoggingMoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/logging-more.php', 'logging-more');

        $this->app->singleton(LoggingMoreUidProcessor::class, function ()
        {
            return new LoggingMoreUidProcessor(32);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/logging-more.php' => config_path('logging-more.php'),
        ], 'logging-more-config');
    }
}
