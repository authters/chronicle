<?php

namespace Authters\Chronicle\Support\Projection;

use Authters\Chronicle\Stream\StreamName;
use Prooph\Common\Messaging\Message;

class InternalProjectionName
{
    const CATEGORY_PREFIX = '$ct-';
    const MESSAGE_NAME_PREFIX = '$mn-';

    /**
     * @var string|null
     */
    protected $name;

    private function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public static function fromCategory(StreamName $streamName): self
    {
        $pos = \strpos($streamName->toString(), '-');
        if (false === $pos) {
            return new self(null);
        }

        $category = \substr($streamName, 0, $pos);

        return new self(self::CATEGORY_PREFIX . $category);
    }

    public static function fromMessageName(Message $message): self
    {
        return new self(self::MESSAGE_NAME_PREFIX . $message->messageName());
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function isValid(): bool
    {
        return null !== $this->name;
    }
}