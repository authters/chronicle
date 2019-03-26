<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Projection\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector as BaseProjector;

class QueryProjectorFactory extends ProjectorFactory
{
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

    protected function project(): BaseProjector
    {
        return new QueryProjector($this->context, $this->runner);
    }
}