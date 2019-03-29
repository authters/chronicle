<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Exceptions\InvalidArgumentException;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector as BaseProjector;

class QueryProjector implements BaseProjector
{
    /**
     * @var QueryProjectorContext
     */
    private $context;

    /**
     * @var QueryProjectorRunner
     */
    private $runner;

    public function __construct(QueryProjectorContext $context, QueryProjectorRunner $runner)
    {
        $this->context = $context;
        $this->runner = $runner;
    }

    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = false): void
    {
        if ($keepRunning) {
            throw new InvalidArgumentException("Query projection run only once");
        }

        ($this->context)($this, $this->context->currentStreamName());

        $this->runner->run();
    }

    public function reset(): void
    {
        $this->context->streamPositions()->reset();

        $callback = $this->context->initCallback();

        if (\is_callable($callback)) {
            $callback = $callback();

            if(\is_array($callback)){
                $this->context->setState($callback());

                return;
            }
        }

        $this->context->resetState();
    }

    public function stop(): void
    {
        $this->context->stop(true);
    }

    public function getState(): array
    {
        return $this->context->state();
    }
}