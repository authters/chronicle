<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Json;
use Authters\Chronicle\Support\Projection\LockTime;
use DateTimeImmutable;

class ProjectorLock
{
    /**
     * @var ProjectorBuilder
     */
    protected $builder;

    /**
     * @var ProjectionProvider
     */
    protected $projectionProvider;

    /**
     * @var ProjectorMutable
     */
    protected $mutable;

    /**
     * @var ProjectorOptions
     */
    protected $options;


    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateTimeImmutable
     */
    protected $lastLockUpdate;

    public function __construct(ProjectorBuilder $builder,
                                ProjectionProvider $projectionProvider,
                                ProjectorMutable $mutable,
                                ProjectorOptions $options,
                                string $name)
    {
        $this->builder = $builder;
        $this->projectionProvider = $projectionProvider;
        $this->mutable = $mutable;
        $this->options = $options;
        $this->name = $name;
    }

    /**
     * @throws \Exception
     */
    public function acquireLock(): void
    {
        $now = LockTime::fromNow();
        $nowString = $now->toString();
        $lockUntilString = $now->createLockUntil($this->options->lockTimeoutMs);

        $this->projectionProvider->acquireLock(
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

        $lockedUntil = $now->createLockUntil($this->options->lockTimeoutMs);

        $this->projectionProvider->updateStatus($this->name, [
            'locked_until' => $lockedUntil,
            'position' => Json::encode($this->mutable->streamPositions())
        ]);

        $this->lastLockUpdate = $now->toDate();
    }

    public function releaseLock(): void
    {
        $this->projectionProvider->updateStatus($this->name, [
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

        $this->projectionProvider->updateStatus($this->name, [
            'status' => $newStatus->getValue(),
            'locked_until' => $now->createLockUntil($this->options->lockTimeoutMs)
        ]);

        $this->mutable->setStatus($newStatus);
        $this->lastLockUpdate = $now->toDate();
    }

    public function createProjection(): void
    {
        $this->projectionProvider->newProjection(
            $this->name,
            $this->mutable->status()->getValue()
        );
    }

    public function load(): void
    {
        $result = $this->projectionProvider->findByName($this->name);

        $this->mutable->streamPositions()->merge(Json::decode($result->getPosition()));

        $state = Json::decode($result->getState());

        if (!empty($state)) {
            $this->mutable->setState($state);
        }
    }

    /**
     * @throws \Exception
     */
    public function stop(): void
    {
        $this->persist();

        $this->mutable->stop(true);

        $newStatus = ProjectionStatus::IDLE();

        $this->projectionProvider->updateStatus($this->name, [
            'status' => $newStatus->getValue()
        ]);

        $this->mutable->setStatus($newStatus);
    }

    public function fetchRemoteStatus(): ProjectionStatus
    {
        $result = $this->projectionProvider->findByName($this->name);

        if (!$result) {
            return ProjectionStatus::RUNNING();
        }

        return $result->getStatus();
    }

    public function projectionExists(): bool
    {
        return $this->projectionProvider->projectionExists($this->name);
    }

    /**
     * @param DateTimeImmutable $now
     * @return bool
     * @throws \Exception
     */
    public function shouldUpdateLock(DateTimeImmutable $now): bool
    {
        if (null === $this->lastLockUpdate || 0 === $this->options->updateLockThreshold) {
            return true;
        }

        $intervalSeconds = \floor($this->options->updateLockThreshold / 1000);
        $updateLockThreshold = new \DateInterval("PT{$intervalSeconds}S");
        $updateLockThreshold->f = ($this->options->updateLockThreshold % 1000) / 1000;
        $threshold = $this->lastLockUpdate->add($updateLockThreshold);

        return $threshold <= $now;
    }
}