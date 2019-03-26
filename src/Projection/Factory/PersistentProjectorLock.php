<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Json;
use Authters\Chronicle\Support\Projection\LockTime;
use DateTimeImmutable;

abstract class PersistentProjectorLock
{
    /**
     * @var ProjectionProvider
     */
    protected $provider;

    /**
     * @var ProjectorContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateTimeImmutable
     */
    protected $lastLockUpdate;

    public function __construct(ProjectionProvider $projectionProvider,
                                ProjectorContext $builder,
                                string $name)
    {
        $this->provider = $projectionProvider;
        $this->context = $builder;
        $this->name = $name;
    }

    /**
     * @throws \Exception
     */
    public function acquireLock(): void
    {
        $now = LockTime::fromNow();
        $nowString = $now->toString();
        $lockUntilString = $this->createLockUntilString($now);

        $this->provider->acquireLock(
            $this->name,
            ProjectionStatus::RUNNING,
            $lockUntilString,
            $nowString
        );

        $this->context->setStatus(ProjectionStatus::RUNNING());
        $this->lastLockUpdate = $now->toDate();
    }

    /**
     * @throws \Exception
     */
    public function updateLock(): void
    {
        $now = LockTime::fromNow();

        if (!$this->shouldUpdateLock($now->toDate())) {
            return;
        }

        $lockedUntil = $this->createLockUntilString($now);

        $this->provider->updateStatus($this->name, [
            'locked_until' => $lockedUntil,
            'position' => Json::encode($this->context->streamPositions()->all())
        ]);

        $this->lastLockUpdate = $now->toDate();
    }

    public function releaseLock(): void
    {
        $this->provider->updateStatus($this->name, [
            'status' => ProjectionStatus::IDLE,
            'locked_until' => null
        ]);

        $this->context->setStatus(ProjectionStatus::IDLE());
    }

    /**
     * @throws \Exception
     */
    public function startAgain(): void
    {
        $this->context->stop(false);
        $newStatus = ProjectionStatus::RUNNING();
        $now = LockTime::fromNow();

        $this->provider->updateStatus($this->name, [
            'status' => $newStatus->getValue(),
            'locked_until' => $this->createLockUntilString($now)
        ]);

        $this->context->setStatus($newStatus);
        $this->lastLockUpdate = $now->toDate();
    }

    public function createProjection(): void
    {
        $this->provider->newProjection(
            $this->name,
            $this->context->status()->getValue()
        );
    }

    public function load(): void
    {
        $result = $this->provider->findByName($this->name);

        $this->context->streamPositions()->mergeReverse(
            Json::decode($result->getPosition())
        );

        $state = Json::decode($result->getState());

        if (!empty($state)) {
            $this->context->setState($state);
        }
    }

    /**
     * @throws \Exception
     */
    public function stop(): void
    {
        $this->persist();

        $this->context->stop(true);

        $newStatus = ProjectionStatus::IDLE();

        $this->provider->updateStatus($this->name, [
            'status' => $newStatus->getValue()
        ]);

        $this->context->setStatus($newStatus);
    }

    public function fetchRemoteStatus(): ProjectionStatus
    {
        $result = $this->provider->findByName($this->name);

        if (!$result) {
            return ProjectionStatus::RUNNING();
        }

        return $result->getStatus();
    }

    public function projectionExists(): bool
    {
        return $this->provider->projectionExists($this->name);
    }

    /**
     * @throws \Exception
     */
    public function persist(): void
    {
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
            $this->deleteEmittedEvents();
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
        $this->context->resetState();
        $callback = $this->context->initCallback();

        if (\is_callable($callback)) {
            $this->context->setState($callback());
        }

        $this->provider->updateStatus($this->name, [
            'position' => Json::encode($this->context->streamPositions()->all()),
            'state' => Json::encode($this->context->state()),
            'status' => $this->context->status()->getValue()
        ]);
    }

    /**
     * @param DateTimeImmutable $now
     * @return bool
     * @throws \Exception
     */
    public function shouldUpdateLock(DateTimeImmutable $now): bool
    {
        $threshold = $this->context->options()->updateLockThreshold;

        if (null === $this->lastLockUpdate || 0 === $threshold) {
            return true;
        }

        $intervalSeconds = \floor($threshold / 1000);
        $updateLockThreshold = new \DateInterval("PT{$intervalSeconds}S");
        $updateLockThreshold->f = ($threshold % 1000) / 1000;
        $threshold = $this->lastLockUpdate->add($updateLockThreshold);

        return $threshold <= $now;
    }

    protected function createLockUntilString(LockTime $dateTime): string
    {
        return $dateTime->createLockUntil(
            $this->context->options()->lockTimeoutMs
        );
    }

    abstract protected function deleteEmittedEvents(): void;
}