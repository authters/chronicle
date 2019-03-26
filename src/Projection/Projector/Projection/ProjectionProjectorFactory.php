<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

class ProjectionProjectorFactory extends ProjectorFactory implements PersistentProjectorFactory
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var ProjectionProjector
     */
    private $projector;

    /**
     * @var ProjectionProjectorLock
     */
    private $lock;

    /**
     * @var ProjectionProjectorRunner
     */
    private $runner;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ProjectionProjectorContext
     */
    protected $context;

    public function __construct(ProjectionProjectorContext $context,
                                Publisher $publisher,
                                ProjectionProjectorLock $lock,
                                ProjectionProjectorRunner $runner,
                                string $name)
    {
        parent::__construct($context);

        $this->publisher = $publisher;
        $this->lock = $lock;
        $this->runner = $runner;
        $this->name = $name;
    }

    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = true): void
    {
        if (!$this->projector) {
            $this->projector = new ProjectionProjector(
                $this->publisher,
                $this->context,
                $this->lock,
                $this->runner,
                $this->name
            );
        }

        $this->projector->run($keepRunning);
    }

    public function stop(): void
    {
        $this->projector->stop();
    }

    public function reset(): void
    {
        $this->projector->reset();
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->projector->delete($deleteEmittedEvents);
    }

    public function getState(): array
    {
        return $this->projector->getState();
    }

    public function getName(): string
    {
        return $this->name;
    }
}