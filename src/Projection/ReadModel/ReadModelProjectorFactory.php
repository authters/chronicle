<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContextBuilder;
use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Projection\ProjectorMutable;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

class ReadModelProjectorFactory extends ProjectorFactory
{
    /**
     * @var ProjectorConnector
     */
    private $connector;

    /**
     * @var ReadModel
     */
    private $readModel;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ReadModelProjectorContextBuilder
     */
    protected $projectorBuilder;

    public function __construct(ProjectorContextBuilder $projectorBuilder,
                                ProjectorConnector $connector,
                                ReadModel $readModel,
                                string $name)
    {
        parent::__construct($projectorBuilder);

        $this->connector = $connector;
        $this->readModel = $readModel;
        $this->name = $name;
    }

    final public function project(): ReadModelProjector
    {
        $mutable = new ProjectorMutable();

        $lock = new ReadModelProjectorLock(
            $this->projectorBuilder,
            $this->connector->projectionProvider(),
            $mutable,
            $this->name,
            $this->readModel
        );

        $runner = new ReadModelProjectorRunner(
            $this->connector,
            $this->projectorBuilder,
            $lock,
            $mutable,
            $this->readModel
        );

        $projector = new ReadModelProjector(
            $mutable,
            $lock,
            $runner,
            $this->readModel,
            $this->name
        );

        // apply context
        $result = ($this->projectorBuilder)($projector, $mutable->currentStreamName());
        $mutable->setState($result);

        return $projector;
    }
}