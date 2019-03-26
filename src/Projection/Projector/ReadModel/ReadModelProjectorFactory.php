<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjectorFactory as BaseProjectorFactory;

final class ReadModelProjectorFactory extends ProjectorFactory implements BaseProjectorFactory
{
    /**
     * @var ReadModelProjector
     */
    private $projector;

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

    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = true): void
    {
        if (!$this->projector) {
            $this->projector = $this->newProjectorInstance();
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

    public function readModel(): ReadModel
    {
        return $this->readModel;
    }

    private function newProjectorInstance(): ReadModelProjector
    {
        return new ReadModelProjector(
            $this->context,
            $this->lock,
            $this->runner,
            $this->readModel,
            $this->name
        );
    }
}