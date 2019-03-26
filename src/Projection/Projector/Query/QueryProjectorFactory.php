<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector as BaseProjector;

class QueryProjectorFactory extends ProjectorFactory implements BaseProjector
{
    /**
     * @var QueryProjector
     */
    private $queryProjector;

    /**
     * @var QueryProjectorRunner
     */
    private $runner;

    /**
     * @var QueryProjectorContext
     */
    protected $context;

    public function __construct(QueryProjectorContext $context, QueryProjectorRunner $runner)
    {
        parent::__construct($context);

        $this->runner = $runner;
    }

    /**
     * @param bool $keepRunning Always false
     * @throws \Exception
     */
    public function run(bool $keepRunning = false): void
    {
        if (!$this->queryProjector) {
            $this->queryProjector = new QueryProjector($this->context, $this->runner);
        }

        $this->queryProjector->run(false);
    }

    public function reset(): void
    {
        $this->queryProjector->reset();
    }

    public function stop(): void
    {
        $this->queryProjector->stop();
    }

    public function getState(): array
    {
        return $this->queryProjector->getState();
    }
}