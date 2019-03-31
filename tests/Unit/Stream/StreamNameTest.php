<?php

namespace AuthtersTest\Chronicle\Unit\Stream;

use Authters\Chronicle\Exceptions\RuntimeException;
use Authters\Chronicle\Stream\StreamName;
use AuthtersTest\Chronicle\Unit\TestCase;

class StreamNameTest extends TestCase
{
    /**
     * @test
     */
    public function it_produce_string(): void
    {
        $st = new StreamName('foo');

        $this->assertEquals('foo', $st->toString());
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_raise_exception_if_name_is_empty(): void
    {
        $st = new StreamName("");
    }
}