<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Json;
use Authters\Chronicle\Support\Projection\LockTime;
use DateTimeImmutable;

abstract class ProjectorLock
{
    /**
     * @var ProjectionProvider
     */
    protected $provider;

    /**
     * @var ProjectorMutable
     */
    protected $mutable;

    /**
     * @var ProjectorContextBuilder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateTimeImmutable
     */
    protected $lastLockUpdate;

    public function __construct(ProjectionProvider $projectionProvider,
                                ProjectorMutable $mutable,
                                ProjectorContextBuilder $builder,
                                string $name)
    {
        $this->provider = $projectionProvider;
        $this->mutable = $mutable;
        $this->builder = $builder;
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

        $this->mutable->setStatus(ProjectionStatus::RUNNING());
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
            'position' => Json::encode($this->mutable->streamPositions()->all())
        ]);

        $this->lastLockUpdate = $now->toDate();
    }

    public function releaseLock(): void
    {
        $this->provider->updateStatus($this->name, [
            'status' => ProjectionStatus::IDLE,
            'locked_until' => null
        ]);

        $this->mutable->setStatus(ProjectionStatus::IDLE());
    }

    /**
     * @throws \Exception
     */
    public function startAgain(): void
    {
        $this->mutable->stop(false);
        $newStatus = ProjectionStatus::RUNNING();
        $now = LockTime::fromNow();

        $this->provider->updateStatus($this->name, [
            'status' => $newStatus->getValue(),
            'locked_until' => $this->createLockUntilString($now)
        ]);

        $this->mutable->setStatus($newStatus);
        $this->lastLockUpdate = $now->toDate();
    }

    public function createProjection(): void
    {
        $this->provider->newProjection(
            $this->name,
            $this->mutable->status()->getValue()
        );
    }

    public function load(): void
    {
        $result = $this->provider->findByName($this->name);

        $this->mutable->streamPositions()->mergeReverse(
            Json::decode($result->getPosition())
        );

        $state = Json::decode($result->getState());

        if (!empty($state)) {
            $this->mutable->setState($state);
        }
    }

    public function stop(): void
    {
        $this->persist();

        $this->mutable->stop(true);

        $newStatus = ProjectionStatus::IDLE();

        $this->provider->updateStatus($this->name, [
            'status' => $newStatus->getValue()
        ]);

        $this->mutable->setStatus($newStatus);
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
     * @param DateTimeImmutable $now
     * @return bool
     * @throws \Exception
     */
    public function shouldUpdateLock(DateTimeImmutable $now): bool
    {
        $threshold = $this->builder->options()->updateLockThreshold;

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
            $this->builder->options()->lockTimeoutMs
        );
    }

    abstract public function persist(): void;

    abstract public function reset(): void;

    abstract public function delete(bool $inclEvents): void;
}