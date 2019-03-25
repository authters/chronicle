<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector as BaseProjector;

class ReadModelProjector implements BaseProjector
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
    private $context;

    public function __construct(ReadModelProjectorContext $context,
                                ReadModelProjectorLock $lock,
                                ReadModelProjectorRunner $runner,
                                ReadModel $readModel,
                                string $name)
    {
        $this->context = $context;
        $this->lock = $lock;
        $this->runner = $runner;
        $this->readModel = $readModel;
        $this->name = $name;
    }

    public function run(bool $keepRunning = true): void
    {
        // apply events context
        $result = ($this->context)($this, $this->context->currentStreamName());
        $this->context->setState($result);

        $this->runner->run($keepRunning);
    }

    public function reset(): void
    {
        $this->lock->reset();
    }

    public function stop(): void
    {
        $this->lock->stop();
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->lock->delete($deleteEmittedEvents);
    }

    public function readModel(): ReadModel
    {
        return $this->readModel;
    }

    public function getState(): array
    {
        return $this->context->state();
    }

    public function getName(): string
    {
        return $this->name;
    }
}