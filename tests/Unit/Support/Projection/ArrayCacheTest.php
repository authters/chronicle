<?php

namespace AuthtersTest\Chronicle\Unit\Support\Projection;

use Authters\Chronicle\Support\Projection\ArrayCache;
use AuthtersTest\Chronicle\Unit\TestCase;

class ArrayCacheTest extends TestCase
{
    /**
     * @test
     */
    public function it_instantiated_with_integer_size(): void
    {
        $cache = new ArrayCache(5);

        $this->assertEquals(5, $cache->size());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Size must be a positive integer
     */
    public function it_raise_exception_when_integer_is_not_greater_than_one(): void
    {
        new ArrayCache(-1);
    }

    /**
     * @test
     */
    public function it_set_value_in_next_available_position(): void
    {
        $cache = new ArrayCache(2);
        $cache->rollingAppend('foo');
        $cache->rollingAppend('bar');

        $this->assertEquals('foo', $cache->get(0));
        $this->assertEquals('bar', $cache->get(1));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Position must be between 0 and 1
     */
    public function it_raise_exception_when_get_position_value_is_less_than_one(): void
    {
        $cache = new ArrayCache(2);
        $cache->rollingAppend('foo');
        $cache->rollingAppend('bar');

        $this->assertEquals('foo', $cache->get(-2));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Position must be between 0 and 1
     */
    public function it_raise_exception_when_get_position_value_is_greater_than_size(): void
    {
        $cache = new ArrayCache(2);
        $cache->rollingAppend('foo');
        $cache->rollingAppend('bar');

        $this->assertEquals('foo', $cache->get(2));
    }

    /**
     * @test
     */
    public function it_can_strictly_check_if_value_exists_in_cache(): void
    {
        $cache = new ArrayCache(2);

        $this->assertFalse($cache->has('foo'));

        $cache->rollingAppend('foo');

        $this->assertTrue($cache->has('foo'));

        $this->assertFalse($cache->has('FOO'));
    }
}