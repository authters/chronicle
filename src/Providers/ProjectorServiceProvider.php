<?php

namespace Authters\Chronicle\Providers;

use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Illuminate\Support\ServiceProvider;

class ProjectorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = config('chronicle.projection');

        if (true === ($config['only_for_console'] ?? true) && !$this->app->runningInConsole()) {
            return;
        }

        $projectorManager = $config['manager'];
        if (!class_exists($projectorManager)) {
            throw new \RuntimeException("Projector manager must be a FQCN");
        }

        // fixMe projection provider is bound in publisher SP
        $this->app->bind(ProjectionManager::class, $projectorManager);

        if($projectionCommands = $config['commands']){
            $this->commands($projectionCommands);
        }
    }
}