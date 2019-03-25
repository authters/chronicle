<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Projection\ProjectorLock;
use Authters\Chronicle\Projection\ProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;

class ReadModelProjectorFactory extends ProjectorFactory
{
    /**
     * @var ProjectorLock
     */
    private $lock;

    /**
     * @var ProjectorRunner
     */
    private $runner;

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
    protected $context;

    public function __construct(ReadModelProjectorContext $context,
                                ReadModelProjectorLock $lock,
                                ReadModelProjectorRunner $runner,
                                ReadModel $readModel,
                                string $name)
    {
        parent::__construct($context);

        $this->lock = $lock;
        $this->runner = $runner;
        $this->readModel = $readModel;
        $this->name = $name;
    }

    final public function project(): ReadModelProjector
    {
        return new ReadModelProjector($this->context, $this->lock, $this->runner, $this->readModel, $this->name);
    }
}