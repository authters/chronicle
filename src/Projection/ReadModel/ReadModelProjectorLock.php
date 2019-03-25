<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContextBuilder;
use Authters\Chronicle\Projection\ProjectorLock;
use Authters\Chronicle\Projection\ProjectorMutable;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Json;
use Authters\Chronicle\Support\Projection\LockTime;

final class ReadModelProjectorLock extends ProjectorLock
{
    /**
     * @var ReadModel
     */
    private $readModel;

    public function __construct(ProjectorContextBuilder $builder,
                                ProjectionProvider $projectionProvider,
                                ProjectorMutable $mutable,
                                string $name,
                                ReadModel $readModel)
    {
        parent::__construct($projectionProvider, $mutable, $builder,$name);

        $this->readModel = $readModel;
    }

    /**
     * @throws \Exception
     */
    public function persist(): void
    {
        $this->readModel->persist();

        $now = LockTime::fromNow();
        $lockUntilString = $this->createLockUntilString($now);

        $this->provider->updateStatus($this->name, [
            'position' => Json::encode($this->mutable->streamPositions()->all()),
            'state' => Json::encode($this->mutable->state()),
            'locked_until' => $lockUntilString
        ]);
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->provider->deleteByName($this->name);

        if ($deleteEmittedEvents) {
            $this->readModel->delete();
        }

        $this->mutable->stop(true);

        $this->mutable->resetState();

        $callback = $this->builder->initCallback();

        if (\is_callable($callback)) {
            $this->mutable->setState($callback());
        }

        $this->mutable->streamPositions()->reset();
    }

    public function reset(): void
    {
        $this->mutable->streamPositions()->reset();

        $callback = $this->builder->initCallback();

        $this->readModel->reset();

        $this->mutable->resetState();

        if (\is_callable($callback)) {
            $this->mutable->setState($callback());
        }

        $this->provider->updateStatus($this->name, [
            'position' => Json::encode($this->mutable->streamPositions()->all()),
            'state' => Json::encode($this->mutable->state()),
            'status' => $this->mutable->status()->getValue()
        ]);
    }
}