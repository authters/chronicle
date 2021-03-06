<?php

namespace Authters\Chronicle\Providers;

use Authters\Chronicle\Aggregate\AggregateType;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcherAggregate;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\StreamNamingStrategy;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AggregateRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $repositories = $this->fromConfig('chronicling.aggregate_repositories');

        $connectionDriver = $this->fromConfig('chronicling.default');

        foreach ($repositories as $repository) {
            $this->createRepository($repository, $connectionDriver);
        }
    }

    protected function createRepository(array $repository, string $defaultDriver): void
    {
        $repositoryValue = $repository['concrete'];

        $repositoryAbstract = null;
        $repositoryConcrete = $repositoryValue;
        if (is_array($repositoryValue)) {
            [$repositoryAbstract, $repositoryConcrete] = $repositoryValue;
        }

        if (!class_exists($repositoryConcrete)) {
            throw new RuntimeException("Repository id $repositoryConcrete must be a class");
        }

        $repositoryAlias = $repositoryAbstract ?? $repositoryConcrete;

        $namingStrategy = $this->determineNamingStrategy($defaultDriver);
        $metadataMatchers = $this->determineMetadataMatchers($defaultDriver);

        $this->app->singleton($repositoryAlias,
            function (Application $app) use ($repository, $repositoryConcrete, $namingStrategy, $metadataMatchers) {
                $streamName = new StreamName($repository['stream_name'] ?? null);

                /** @var StreamNamingStrategy $namingStrategyInstance */
                $namingStrategyInstance = new $namingStrategy($streamName);

                if (!$namingStrategyInstance->isOneStreamPerAggregate() && !$metadataMatchers) {
                    throw new RuntimeException(
                        "Metadata matchers is mandatory combined with single stream naming strategy"
                    );
                }

                return new $repositoryConcrete(
                    $app->get(Chronicler::class),
                    AggregateType::fromRootClass($repository['type']),
                    $namingStrategyInstance,
                    $metadataMatchers ? $app->get($metadataMatchers) : null
                );
            });
    }

    protected function determineNamingStrategy(string $driver): string
    {
        $strategy = $this->fromConfig("connections.chronicler.{$driver}.naming_strategy");

        if (!is_string($strategy) || !class_exists($strategy)) {
            throw new RuntimeException("Invalid Naming strategy from Chronicle config");
        }

        return $strategy;
    }

    protected function determineMetadataMatchers(string $driver): ?string
    {
        $metadataMatchers = $this->fromConfig("connections.chronicler.{$driver}.metadata_matchers");

        if (!$metadataMatchers) {
            return null;
        }

        $concrete = $metadataMatchers;
        $alias = MetadataMatcherAggregate::class;

        if (\is_array($concrete)) {
            [$alias, $concrete] = $concrete;
        }

        if (!class_exists($concrete)) {
            throw new RuntimeException("Metadata Matchers concrete class not found in Chronicler config");
        }

        $this->app->bind($alias, $concrete);

        return $alias;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    protected function fromConfig(string $key = null)
    {
        $config = $this->app->get('config')->get('chronicle');

        if (!$config) {
            throw new RuntimeException("Chronicle configuration not found");
        }

        return $key ? Arr::get($config, $key) : $config;
    }
}