<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjectorFactory as BaseProjectorFactory;

final class ReadModelProjectorFactory extends ProjectorFactory implements BaseProjectorFactory, PersistentProjector
{
    /**
     * @var ReadModelProjector
     */
    private $projector;

    /**
     * @var ReadModelPersistentProjectorLock
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
                                ReadModelPersistentProjectorLock $lock,
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

    public function run(bool $keepRunning = true): void
    {
        if (!$this->projector) {
            $this->projector = new ReadModelProjector(
                $this->context,
                $this->lock,
                $this->runner,
                $this->readModel,
                $this->name
            );
        }

        $this->projector->run($keepRunning);
    }

    public function reset(): void
    {
        $this->projector->reset();
    }

    public function stop(): void
    {
        $this->projector->stop();
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->projector->delete($deleteEmittedEvents);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getState(): array
    {
        return $this->projector->getState();
    }
}