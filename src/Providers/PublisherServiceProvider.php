<?php

namespace Authters\Chronicle\Providers;

use Authters\Chronicle\Chronicler\Tracker\EventTracker;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class PublisherServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    public function register(): void
    {
        $driver = $this->fromConfig('chronicler.default');
        $config = $this->fromConfig("connections.chronicler.{$driver}");

        if (!$config) {
            throw new \RuntimeException("Chronicler driver $driver not found");
        }

        $publisher = $config['chronicler'];
        if (!class_exists($publisher)) {
            throw new RuntimeException("Chronicler from chronicle config must be a FQCN");
        }

        $persistenceStrategy = $config['persistence_strategy'];
        if (!class_exists($persistenceStrategy)) {
            throw new RuntimeException("Persistence strategy from chronicle config must be a FQCN");
        }

        $this->registerPublisher($publisher, $persistenceStrategy);
    }

    protected function registerPublisher(string $publisher, string $persistenceStrategy): void
    {
        $config = $this->fromConfig('chronicler');

        // move projection provider binding to projection manager
        // need a tracker service id, alias or contract for event tracker
        // need logic for connection used
        // need logic for transaction
        // message factory could already be bound in ioc by service bus

        [$projectionProvider, $eventStreamProvider] = $this->registerPublisherProviders($config['providers']);
        $decorator = $this->determinePublisherDecorator($config['decorator']);
        $messageFactory = $this->determineMessageFactory($config['message_factory']);
        $batchSize = $config['batch_size'] ?? 10000;
        $disableTransactionHandling = !$config['use_transaction'];

        /** @var Chronicler $publisher */
        $publisherInstance = new $publisher(
            $this->app->get(Connection::class),
            $this->app->get($messageFactory),
            $this->app->get($persistenceStrategy),
            $this->app->get($eventStreamProvider),
            $disableTransactionHandling,
            $batchSize
        );

        $decoratorInstance = new $decorator($publisherInstance, $this->app->get(EventTracker::class));

        $this->app->instance(Chronicler::class, $decoratorInstance);
    }

    protected function determinePublisherDecorator(array $config): string
    {
        $eventPublisher = $config['event_chronicler'];
        if (!class_exists($eventPublisher)) {
            throw new RuntimeException("Decorator event chronicler from chronicle config must be a FQCN");
        }

        if ($transactionalPublisher = $config['transactional_chronicler'] ?? null) {
            if (!class_exists($transactionalPublisher)) {
                throw new RuntimeException("Decorator transactional event chronicler from chronicle config must be a FQCN");
            }

            if (!is_subclass_of($transactionalPublisher, $eventPublisher)) {
                throw new RuntimeException("Decorator transactional event publisher must be a subclass of {$eventPublisher}");
            }

            $eventPublisher = $transactionalPublisher;
        }

        return $eventPublisher;
    }

    protected function registerPublisherProviders(array $config): array
    {
        $this->app->bind(ProjectionProvider::class, $config['projection']);

        $this->app->bind(EventStreamProvider::class, $config['event_stream']);

        return [ProjectionProvider::class, EventStreamProvider::class];
    }

    protected function determineMessageFactory(string $messageFactory = null): string
    {
        if (!$messageFactory || !class_exists($messageFactory)) {
            throw new RuntimeException("Invalid message factory service from chronicle config");
        }

        return $messageFactory;
    }


    /**
     * @param string|null $key
     * @return mixed
     */
    protected function fromConfig(string $key = null)
    {
        $config = $this->app->get('config')->get('chronicle');

        if (!$config) {
            throw new \RuntimeException("Chronicle configuration not found");
        }

        return $key ? Arr::get($config, $key) : $config;
    }

    public function provides(): array
    {
        return [Chronicler::class, ProjectionProvider::class, EventStreamProvider::class];
    }
}