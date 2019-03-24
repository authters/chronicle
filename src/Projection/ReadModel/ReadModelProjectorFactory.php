<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContextBuilder;
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
        $projector = new ReadModelProjector(
            $this->connector,
            $this->readModel,
            $this->projectorBuilder,
            $this->name
        );

        return $projector;
    }
}