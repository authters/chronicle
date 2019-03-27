<?php

namespace Authters\Chronicle;

use Authters\Chronicle\Providers\AggregateRepositoryServiceProvider;
use Authters\Chronicle\Providers\EventTrackerServiceProvider;
use Authters\Chronicle\Providers\ProjectorServiceProvider;
use Authters\Chronicle\Providers\PublisherServiceProvider;
use Illuminate\Support\AggregateServiceProvider;

class ChronicleServiceProvider extends AggregateServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        EventTrackerServiceProvider::class,
        AggregateRepositoryServiceProvider::class,
        PublisherServiceProvider::class,
        ProjectorServiceProvider::class,
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

        // fixMe
        $driver = config('chronicle.publisher.default');
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