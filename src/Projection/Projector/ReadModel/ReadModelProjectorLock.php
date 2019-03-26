<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\Factory\PersistentProjectorLock;
use Authters\Chronicle\Projection\Factory\ProjectorContext;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;

final class ReadModelProjectorLock extends PersistentProjectorLock
{
    /**
     * @var ReadModel
     */
    private $readModel;

    public function __construct(ProjectorContext $builder,
                                ProjectionProvider $projectionProvider,
                                string $name,
                                ReadModel $readModel)
    {
        parent::__construct($projectionProvider, $builder, $name);

        $this->readModel = $readModel;
    }

    /**
     * @throws \Exception
     */
    public function persist(): void
    {
        $this->readModel->persist();

        parent::persist();
    }


    public function reset(): void
    {
        $this->readModel->reset();

        parent::reset();
    }

    protected function deleteEmittedEvents(): void
    {
        $this->readModel->delete();
    }
}