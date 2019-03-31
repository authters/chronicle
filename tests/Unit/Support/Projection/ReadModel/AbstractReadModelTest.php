<?php

namespace AuthtersTest\Chronicle\Unit\Support\Projection\ReadModel;

use Authters\Chronicle\Exceptions\InvalidArgumentException;
use AuthtersTest\Chronicle\Unit\Mock\ReadModelMock;
use AuthtersTest\Chronicle\Unit\TestCase;

class AbstractReadModelTest extends TestCase
{
    /**
     * @test
     */
    public function it_check_if_model_is_initialized(): void
    {
        $rm = new ReadModelMock();

        $this->assertFalse($rm->isInitialized());

        $rm->init();
        $this->assertTrue($rm->isInitialized());
    }

    /**
     * @test
     */
    public function it_can_insert_data(): void
    {
        $rm = new ReadModelMock();

        $rm->init();
        $rm->insert('foo', 'bar');

        $this->assertTrue($rm->hasKey('foo'));
    }

    /**
     * @test
     */
    public function it_can_check_if_key_exists(): void
    {
        $rm = new ReadModelMock();

        $rm->init();

        $this->assertFalse($rm->hasKey('foo'));

        $rm->insert('foo', 'bar');
        $this->assertTrue($rm->hasKey('foo'));
    }

    /**
     * @test
     */
    public function it_can_update_data(): void
    {
        $rm = new ReadModelMock();

        $rm->init();

        $rm->insert('foo', 'bar');
        $this->assertTrue($rm->hasKey('foo'));

        $rm->update('foo', 'foo_bar');
        $this->assertEquals('foo_bar', $rm->read('foo'));
    }

    /**
     * @test
     */
    public function it_can_reset_read_model(): void
    {
        $rm = new ReadModelMock();

        $rm->init();

        $rm->insert('foo', 'bar');
        $this->assertTrue($rm->hasKey('foo'));

        $rm->reset();

        $this->assertTrue($rm->isInitialized());
        $this->assertFalse($rm->hasKey('foo'));
    }

    /**
     * @test
     */
    public function it_can_delete_read_model(): void
    {
        $rm = new ReadModelMock();

        $rm->init();

        $rm->insert('foo', 'bar');
        $this->assertTrue($rm->hasKey('foo'));

        $rm->delete();

        $this->assertFalse($rm->isInitialized());
    }

    /**
     * @test
     */
    public function it_can_persist_operations(): void
    {
        $rm = new ReadModelMock();
        $rm->init();

        $rm->stack('insert', 'foo', 'bar');
        $rm->persist();
        $this->assertTrue($rm->hasKey('foo'));

        $rm->stack('update', 'foo', 'baz');
        $this->assertEquals('bar', $rm->read('foo'));
        $rm->persist();
        $this->assertEquals('baz', $rm->read('foo'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_raise_exception_if_key_does_not_exists(): void
    {
        $rm = new ReadModelMock();
        $rm->init();

        $this->assertFalse($rm->hasKey('foo'));

        $rm->update('foo', 'bar');
    }
}