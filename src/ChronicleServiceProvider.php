<?php

namespace Authters\Chronicle;

use Authters\Chronicle\Providers\EventTrackerServiceProvider;
use Illuminate\Support\AggregateServiceProvider;

class ChronicleServiceProvider extends AggregateServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        EventTrackerServiceProvider::class
    ];

    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [$this->getConfigPath() => config_path('chronicle.php')],
            'config'
        );

        // load conditionally from connection config
        $driver = 'mysql';
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/' . $driver);
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'chronicle');
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/chronicle.php';
    }
}