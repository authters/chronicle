<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector as BaseProjector;

class ReadModelProjectorFactory extends ProjectorFactory
{
    /**
     * @var ReadModelProjectorLock
     */
    private $lock;

    /**
     * @var ReadModelProjectorRunner
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

    final public function project(): BaseProjector
    {
        return new ReadModelProjector($this->context, $this->lock, $this->runner, $this->readModel, $this->name);
    }
}