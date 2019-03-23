<?php

namespace Authters\Chronicle\Support\Projection;

use DateTimeImmutable;

class LockTime
{
    const TIMEZONE = 'UTC';

    const FORMAT = 'Y-m-d\TH:i:s.u';

    /**
     * @var DateTimeImmutable
     */
    private $dateTime;

    private function __construct(DateTimeImmutable $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return LockTime
     * @throws \Exception
     */
    public static function fromNow(): self
    {
        $now = new DateTimeImmutable("now", new \DateTimeZone(self::TIMEZONE));

        return new self($now);
    }

    public function createLockUntil(int $lockTimeoutMs): string
    {
        $micros = (string)((int)$this->dateTime->format('u') + ($lockTimeoutMs * 1000));

        $secs = \substr($micros, 0, -6);

        if ('' === $secs) {
            $secs = 0;
        }

        return $this->dateTime
                ->modify('+' . $secs . ' seconds')
                ->format('Y-m-d\TH:i:s') . '.' . \substr($micros, -6);
    }

    public function toDate(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function toString(): string
    {
        return $this->dateTime->format(self::FORMAT);
    }
}