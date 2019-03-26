<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectionProjector as BaseProjector;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Prooph\Common\Messaging\Message;

class ProjectionProjector implements BaseProjector
{
    /**
     * @var ProjectionProjectorContext
     */
    private $context;

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
     * @var Publisher
     */
    private $publisher;

    public function __construct(Publisher $publisher,
                                ProjectionProjectorContext $context,
                                ProjectionProjectorLock $lock,
                                ProjectionProjectorRunner $runner,
                                string $name)
    {
        $this->publisher = $publisher;
        $this->context = $context;
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
        ($this->context)($this, $this->context->currentStreamName());

        $this->runner->run($keepRunning);
    }

    public function emit(Message $event): void
    {
        if (!$this->context->isStreamCreated() && !$this->publisher->hasStream(new StreamName($this->name))) {
            $this->publisher->create(
                new Stream(new StreamName($this->name), new \ArrayIterator())
            );

            $this->context->setStreamCreated(true);
        }

        $this->linkTo($this->name, $event);
    }

    public function linkTo(string $streamName, Message $event): void
    {
        $sn = new StreamName($streamName);

        if ($this->context->cachedStreamNames()->has($streamName)) {
            $append = true;
        } else {
            $this->context->cachedStreamNames()->rollingAppend($streamName);
            $append = $this->publisher->hasStream($sn);
        }

        if ($append) {
            $this->publisher->appendTo($sn, new \ArrayIterator($event));
        } else {
            $this->publisher->create(new Stream($sn, new \ArrayIterator($event)));
        }
    }

    public function stop(): void
    {
        $this->lock->stop();
    }

    public function reset(): void
    {
        $this->lock->reset();
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->lock->delete($deleteEmittedEvents);
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