<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContext;
use Authters\Chronicle\Projection\ProjectorFactory;
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
     * @var ReadModelProjectorContext
     */
    protected $projectorContext;

    public function __construct(ProjectorContext $projectorBuilder,
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
        $lock = new ReadModelProjectorLock(
            $this->projectorContext,
            $this->connector->projectionProvider(),
            $this->name,
            $this->readModel
        );

        $runner = new ReadModelProjectorRunner(
            $this->projectorContext,
            $this->connector,
            $lock,
            $this->readModel
        );

        $projector = new ReadModelProjector(
            $this->projectorContext,
            $lock,
            $runner,
            $this->readModel,
            $this->name
        );

        return $projector;
    }
}