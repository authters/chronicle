<?php

namespace AuthtersTest\Chronicle\Unit\Support\Projection;

use Authters\Chronicle\Support\Projection\EventCounter;
use AuthtersTest\Chronicle\Unit\TestCase;

class EventCounterTest extends TestCase
{
    /**
     * @test
     */
    public function it_start_counter_to_zero_on_instantiation(): void
    {
        $ec = new EventCounter();
        $this->assertEquals(0, $ec->current());
    }

    /**
     * @test
     */
    public function it_increment_counter(): void
    {
        $ec = new EventCounter();
        $this->assertEquals(0, $ec->current());

        $ec->increment();
        $this->assertEquals(1, $ec->current());
    }

    /**
     * @test
     */
    public function it_reset_counter(): void
    {
        $ec = new EventCounter();
        $this->assertEquals(0, $ec->current());

        $ec->increment();
        $this->assertEquals(1, $ec->current());

        $ec->reset();
        $this->assertEquals(0, $ec->current());
    }

    /**
     * @test
     */
    public function it_asert_counter_is_reset(): void
    {
        $ec = new EventCounter();
        $this->assertTrue($ec->isEqualsTo(0));

        $ec->increment();
        $this->assertTrue($ec->isEqualsTo(1));

        $ec->reset();
        $this->assertTrue($ec->isReset());
    }

    /**
     * @test
     */
    public function it_can_be_compared_numerically(): void
    {
        $ec = new EventCounter();
        $this->assertTrue($ec->isEqualsTo(0));

        $ec->increment();
        $this->assertTrue($ec->isEqualsTo(1));
    }
}