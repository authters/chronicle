<?php

namespace AuthtersTest\Chronicle\Integration;

use Authters\Chronicle\Projection\Model\EventStream;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Illuminate\Database\Query\Builder;

class EventStreamModelIntegrationTest extends IntegrationTestCase
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
    public function it_create_event_stream(): void
    {
        $model = new EventStream();
        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $this->assertTrue($model->newEventStream($data));
    }

    /**
     * @test
     */
    public function it_check_if_event_stream_exists(): void
    {
        $model = new EventStream();

        $this->assertFalse($model->hasRealStreamName('foo'));

        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $model->newEventStream($data);

        $this->assertTrue($model->hasRealStreamName('foo'));
    }

    /**
     * @test
     */
    public function it_find_event_stream_by_real_stream_name(): void
    {
        $model = new EventStream();
        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $model->newEventStream($data);

        /** @var EventStream $result */
        $result = $model->filterStreamNames('foo', null);

        $this->assertEquals(['foo'], $result->toArray());
    }

    /**
     * @test
     */
    public function it_filter_event_stream_with_callable_metadata_matcher(): void
    {
        $model = new EventStream();
        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $model->newEventStream($data);

        $callback = $this->callableMetadataMatcher(function (Builder $builder) {
            $builder->where('real_stream_name', 'foo');
        });

        $result = $model->filterStreamNames(null, $callback);

        $this->assertEquals(['foo'], $result->toArray());
    }

    /**
     * @test
     */
    public function it_filter_by_limit_and_offset(): void
    {
        $model = new EventStream();

        $i = 10;
        while ($i !== 0) {
            $data =
                [
                    'real_stream_name' => 'foo_' . $i,
                    'stream_name' => 'bar_' . $i,
                    'metadata' => 'foo_bar',
                    'category' => 'baz'
                ];

            $model->newEventStream($data);

            $i--;
        }

        $this->assertCount(10, $model->filterStreamNames(null, null, 10));

        $this->assertCount(5, $model->filterStreamNames(null, null, 5, 5));
    }

    /**
     * @test
     */
    public function it_filter_internal_event_stream(): void
    {
        $model = new EventStream();

        $i = 10;
        while ($i !== 0) {
            $internalStream =
                [
                    'real_stream_name' => '$foo_' . $i,
                    'stream_name' => 'bar_' . $i,
                    'metadata' => 'foo_bar',
                    'category' => 'baz'
                ];

            $model->newEventStream($internalStream);

            $i--;
        }

        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $model->newEventStream($data);

        $this->assertCount(11, $model->filterStreamNames(null, null));
        $this->assertCount(1, $model->findAllExceptInternalStreams());
    }

    /**
     * @test
     */
    public function it_delete_event_stream(): void
    {
        $model = new EventStream();
        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $model->newEventStream($data);

        $this->assertTrue($model->hasRealStreamName('foo'));

        $model->deleteRealStreamName('foo');

        $this->assertFalse($model->hasRealStreamName('foo'));
    }

    /**
     * @test
     */
    public function it_update_event_stream_metadata(): void
    {
        $model = new EventStream();
        $data =
            [
                'real_stream_name' => 'foo',
                'stream_name' => 'bar',
                'metadata' => 'foo_bar',
                'category' => 'baz'
            ];

        $model->newEventStream($data);
        $callback = $this->callableMetadataMatcher(function (Builder $builder) {
            $builder->where('metadata', 'foo_bar');
        });
        $result = $model->filterStreamNames(null, $callback);
        $this->assertEquals(['foo'], $result->toArray());

        $model->updateStreamMetadata('foo', 'baz_baz');

        $callback = $this->callableMetadataMatcher(function (Builder $builder) {
            $builder->where('metadata', 'baz_baz');
        });
        $result = $model->filterStreamNames(null, $callback);
        $this->assertEquals(['foo'], $result->toArray());
    }

    /**
     * @test
     */
    public function it_filter_by_category(): void
    {
        $model = new EventStream();

        $i = 10;
        while ($i !== 0) {
            $data =
                [
                    'real_stream_name' => 'foo_' . $i,
                    'stream_name' => 'bar_' . $i,
                    'metadata' => 'foo_bar',
                    'category' => 'baz_' . $i
                ];

            $model->newEventStream($data);

            $i--;
        }

        $this->assertEquals(['baz_5'], $model->filterCategoryNames('baz_5', null)->toArray());

        $matcher = $this->callableMetadataMatcher(function(Builder $builder){
            $builder->where('category', 'baz_10');
        });

        $this->assertEquals(['baz_10'], $model->filterCategoryNames(null, $matcher)->toArray());

        $this->assertCount(3, $model->filterCategoryNames(null, null, 20, 7));
    }

    protected function callableMetadataMatcher(callable $callback): MetadataMatcher
    {
        return new class($callback) implements MetadataMatcher
        {
            /**
             * @var callable
             */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function data(): callable
            {
                return $this->callback;
            }
        };
    }
}