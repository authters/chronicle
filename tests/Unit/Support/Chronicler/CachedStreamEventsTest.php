<?php

namespace AuthtersTest\Chronicle\Unit\Support\Chronicler;

use Authters\Chronicle\Support\Chronicler\CachedStreamEvents;
use AuthtersTest\Chronicle\Unit\TestCase;

class CachedStreamEventsTest extends TestCase
{
    /**
     * @test
     */
    public function it_instantiated_with_empty_cached_streams(): void
    {
        $cached = new CachedStreamEvents();

        $this->assertEmpty($cached->streamEvents());
    }

    /**
     * @test
     */
    public function it_can_add_streams(): void
    {
        $cached = new CachedStreamEvents();

        $streams = ['foo', 'bar'];
        $cached->add($streams);

        $this->assertCount(2,$cached->streamEvents());
        $this->assertEquals(['foo','bar'], $cached->streamEvents());
    }

    /**
     * @test
     */
    public function it_can_reset_cached_streams(): void
    {
        $cached = new CachedStreamEvents();

        $streams = ['foo', 'bar'];
        $cached->add($streams);

        $this->assertCount(2,$cached->streamEvents());

        $cached->reset();

        $this->assertEmpty($cached->streamEvents());
    }
}