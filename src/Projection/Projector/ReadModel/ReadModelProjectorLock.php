<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\Factory\ProjectorContext;
use Authters\Chronicle\Projection\Factory\ProjectorLock;
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

        $now = LockTime::fromNow();
        $lockUntilString = $this->createLockUntilString($now);

        $this->provider->updateStatus($this->name, [
            'position' => Json::encode($this->context->streamPositions()->all()),
            'state' => Json::encode($this->context->state()),
            'locked_until' => $lockUntilString
        ]);
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->provider->deleteByName($this->name);

        if ($deleteEmittedEvents) {
            $this->readModel->delete();
        }

        $this->context->stop(true);

        $this->context->resetState();

        $callback = $this->context->initCallback();

        if (\is_callable($callback)) {
            $this->context->setState($callback());
        }

        $this->context->streamPositions()->reset();
    }

    public function reset(): void
    {
        $this->context->streamPositions()->reset();

        $callback = $this->context->initCallback();

        $this->readModel->reset();

        $this->context->resetState();

        if (\is_callable($callback)) {
            $this->context->setState($callback());
        }

        $this->provider->updateStatus($this->name, [
            'position' => Json::encode($this->context->streamPositions()->all()),
            'state' => Json::encode($this->context->state()),
            'status' => $this->context->status()->getValue()
        ]);
    }
}