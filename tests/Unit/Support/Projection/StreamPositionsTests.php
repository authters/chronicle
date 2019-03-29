<?php

namespace AuthtersTest\Chronicle\Unit\Support\Projection;

use Authters\Chronicle\Support\Projection\StreamPositions;
use AuthtersTest\Chronicle\Unit\TestCase;

class StreamPositionsTests extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_instantiated_with_empty_stream_positions(): void
    {
        $sp = new StreamPositions([]);
        $this->assertEmpty($sp->all());
    }

    /**
     * @test
     */
    public function it_prepare_stream_positions(): void
    {
        $sp = new StreamPositions();

        $streams = ['foo', 'bar'];
        $sp->prepareStreamPositions($streams);

        $this->assertEquals([
            'foo' => 0,
            'bar' => 0
        ], $sp->all());
    }

    /**
     * @test
     */
    public function it_merge_stream_positions(): void
    {
        $sp = new StreamPositions();

        $streams = ['foo'];
        $sp->prepareStreamPositions($streams);
        $this->assertEquals(['foo' => 0], $sp->all());

        $sp->merge(['bar' => 0]);
        $this->assertEquals([
            'foo' => 0,
            'bar' => 0
        ], $sp->all());
    }

    /**
     * @test
     */
    public function it_merge_reverse_stream_positions(): void
    {
        $sp = new StreamPositions();

        $streams = ['foo'];
        $sp->prepareStreamPositions($streams);
        $this->assertEquals(['foo' => 0], $sp->all());

        $sp->mergeReverse(['bar' => 0]);
        $this->assertEquals([
            'bar' => 0,
            'foo' => 0,
        ], $sp->all());
    }

    /**
     * @test
     */
    public function it_set_stream_on_defined_position(): void
    {
        $sp = new StreamPositions();

        $streams = ['foo'];
        $sp->prepareStreamPositions($streams);

        $sp->set('bar', 1);

        $this->assertEquals([
            'foo' => 0,
            'bar' => 1
        ], $sp->all());
    }

    /**
     * @test
     */
    public function it_reset_stream_positions(): void
    {
        $sp = new StreamPositions();

        $streams = ['foo', 'bar'];
        $sp->prepareStreamPositions($streams);

        $this->assertEquals([
            'foo' => 0,
            'bar' => 0
        ], $sp->all());

        $sp->reset();

        $this->assertEquals([], $sp->all());
    }

    /**
     * @test
     */
    public function it_assert_stream_positions_is_empty(): void
    {
        $sp = new StreamPositions();
        $this->assertTrue($sp->isEmpty());

        $streams = ['foo', 'bar'];
        $sp->prepareStreamPositions($streams);

        $this->assertEquals([
            'foo' => 0,
            'bar' => 0
        ], $sp->all());

        $this->assertFalse($sp->isEmpty());
    }
}