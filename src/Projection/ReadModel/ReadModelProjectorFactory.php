<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorBuilder;
use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Projection\ProjectorOptions;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

class ReadModelProjectorFactory extends ProjectorFactory
{
    /**
     * @var ProjectorConnector
     */
    private $connector;

    /**
     * @var ProjectorOptions
     */
    private $options;

    /**
     * @var ReadModel
     */
    private $readModel;

    /**
     * @var string
     */
    private $name;

    public function __construct(ProjectorConnector $connector,
                                ProjectorOptions $options,
                                ReadModel $readModel,
                                string $name)
    {
        $this->connector = $connector;
        $this->options = $options;
        $this->readModel = $readModel;
        $this->name = $name;
    }

    final public function run(bool $keepRunning = true): void
    {
        // factory must act a decorator
        $builder = new ProjectorBuilder($this->query, $this->initCallback, $this->handlers);

        $context = function($projector, ?string $streamName){
            return new ReadModelHandlerContext($projector, $streamName);
        };

        $projector = new ReadModelProjector(
            $this->connector,
            $this->options,
            $this->readModel,
            $builder,
            $context,
            $this->name
        );

        $projector->project($keepRunning);
    }
}