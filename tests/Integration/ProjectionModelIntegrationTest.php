<?php

namespace AuthtersTest\Chronicle\Integration;

use Authters\Chronicle\Projection\Factory\ProjectionStatus;
use Authters\Chronicle\Projection\Model\Projection;
use Authters\Chronicle\Support\Json;
use Authters\Chronicle\Support\Projection\LockTime;

class ProjectionModelIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(
            [
                '--database' => 'testing',
                '--path' => realpath(__DIR__ . '/../../database/migrations/mysql'),
            ]
        );
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    /**
     * @test
     */
    public function it_create_projection(): void
    {
        $model = new Projection();
        $created = $model->newProjection('foo', ProjectionStatus::RUNNING);

        $this->assertTrue($created);
    }

    /**
     * @test
     */
    public function it_assert_projection_name_exists(): void
    {
        $model = new Projection();
        $model->newProjection('foo', ProjectionStatus::RUNNING);

        $this->assertTrue($model->projectionExists('foo'));
    }

    /**
     * @test
     */
    public function it_find_by_projection_name(): void
    {
        $model = new Projection();
        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $projection = $model->findByName('foo');

        $result = [
            'no' => 1,
            'name' => $projection->getName(),
            'position' => $projection->getPosition(),
            'state' => $projection->getState(),
            'status' => $projection->getStatus()->getValue(),
            'locked_until' => null,
        ];

        $expected = [
            'no' => 1,
            'name' => "foo",
            'position' => "{}",
            'state' => "{}",
            'status' => ProjectionStatus::RUNNING,
            'locked_until' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_return_all_projections_names(): void
    {
        $model = new Projection();

        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $model->newProjection('bar', ProjectionStatus::RUNNING);

        $result = $model->findByNames('', 20);
        $this->assertCount(2, $result);
    }

    /**
     * @test
     */
    public function it_return_projections_names_by_limit_and_offset(): void
    {
        $model = new Projection();

        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $model->newProjection('bar', ProjectionStatus::RUNNING);
        $model->newProjection('foo_bar', ProjectionStatus::RUNNING);
        $model->newProjection('bar_foo', ProjectionStatus::RUNNING);

        $this->assertCount(4, $model->findByNames('', 20));

        $result = $model->findByNames('', 3);
        $this->assertCount(3, $result);

        $this->assertCount(1, $model->findByNames('', 20, 3));
    }

    /**
     * @test
     */
    public function it_return_projections_names_by_regex(): void
    {
        $this->markTestSkipped('fix regexp');

        $model = new Projection();

        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $model->newProjection('bar', ProjectionStatus::RUNNING);
        $model->newProjection('foo_bar', ProjectionStatus::RUNNING);
        $model->newProjection('bar_foo', ProjectionStatus::RUNNING);
        $model->newProjection('baz_baz', ProjectionStatus::RUNNING);
        $model->newProjection('bar_baz', ProjectionStatus::RUNNING);


        $this->assertCount(3, $model->findByNamesRegex("'^foo'", 20));
    }

    /**
     * @test
     */
    public function it_delete_projection_by_name(): void
    {
        $model = new Projection();

        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $model->newProjection('bar', ProjectionStatus::RUNNING);

        $this->assertTrue($model->projectionExists('foo'));
        $this->assertTrue($model->projectionExists('bar'));

        $model->deleteByName('foo');

        $this->assertTrue($model->projectionExists('bar'));
        $this->assertFalse($model->projectionExists('foo'));
    }

    /**
     * @test
     */
    public function it_update_status(): void
    {
        $model = new Projection();
        $model->newProjection('foo', ProjectionStatus::RUNNING);

        $projection = $model->findByName('foo');

        $result = [
            'no' => 1,
            'name' => $projection->getName(),
            'position' => $projection->getPosition(),
            'state' => $projection->getState(),
            'status' => $projection->getStatus()->getValue(),
            'locked_until' => null,
        ];

        $expected = [
            'no' => 1,
            'name' => "foo",
            'position' => "{}",
            'state' => "{}",
            'status' => ProjectionStatus::RUNNING,
            'locked_until' => null,
        ];

        $this->assertEquals($expected, $result);

        $data = [
            'position' => Json::encode(['baz' => 'foo_bar']),
            'state' => Json::encode(['foo' => 'bar']),
            'status' => ProjectionStatus::IDLE,
            'locked_until' => 'baz_baz',
        ];

        $this->assertEquals(1, $model->updateStatus('foo', $data));

        $projectionUpdated = $model->findByName('foo');

        $expectedUpdated = [
            "no" => 1,
            "name" => "foo",
            "position" => '{"baz":"foo_bar"}',
            "state" => '{"foo":"bar"}',
            "status" => "idle",
            "locked_until" => "baz_baz",
        ];

        $resultUpdated = [
            'no' => 1,
            'name' => $projectionUpdated->getName(),
            'position' => $projectionUpdated->getPosition(),
            'state' => $projectionUpdated->getState(),
            'status' => $projectionUpdated->getStatus()->getValue(),
            'locked_until' => $projectionUpdated->getLockedUntil(),
        ];

        $this->assertEquals($expectedUpdated, $resultUpdated);
    }

    /**
     * @test
     */
    public function it_update_lock_when_remote_lock_is_null(): void
    {
        $model = new Projection();

        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $this->assertTrue($model->projectionExists('foo'));

        $this->assertNull($model->findByName('foo')->getLockedUntil());

        $now = LockTime::fromNow();

        $lockTime = $now
            ->toDate()
            ->modify("+1 day")
            ->format(
                LockTime::FORMAT
            );

        $updatedLock = $model->acquireLock('foo', ProjectionStatus::STOPPING, $lockTime, $now->toString());

        $this->assertEquals(1, $updatedLock);
        $this->assertEquals($lockTime, $model->findByName('foo')->getLockedUntil());
    }

    /**
     * @test
     */
    public function it_update_lock_when_remote_lock_is_lesser_than_now(): void
    {
        $model = new Projection();

        $model->newProjection('foo', ProjectionStatus::RUNNING);
        $this->assertTrue($model->projectionExists('foo'));

        $this->assertNull($model->findByName('foo')->getLockedUntil());

        // up null lock
        $now = LockTime::fromNow();
        $lockTime = $now->toDate()->modify("+1 day");

        $lockTimeString = $lockTime->format(LockTime::FORMAT);

        $this->assertEquals(
            1,
            $model->acquireLock('foo', ProjectionStatus::STOPPING, $lockTimeString, $now->toString())
        );

        $this->assertEquals($lockTimeString, $model->findByName('foo')->getLockedUntil());

        // up less than now
        $futureNow = $now->toDate()->modify("+3 day");
        $futureNowString = $futureNow->format(LockTime::FORMAT);
        $futureLockTimeString = $lockTime->modify("+1 day")->format(LockTime::FORMAT);

        $this->assertEquals(
            1,
            $model->acquireLock('foo', ProjectionStatus::STOPPING, $futureLockTimeString, $futureNowString)
        );

        $this->assertEquals($futureLockTimeString, $model->findByName('foo')->getLockedUntil());

        // update fail
        $this->assertEquals(
            0,
            $model->acquireLock('foo', ProjectionStatus::STOPPING, $lockTimeString, $now->toString())
        );
    }
}