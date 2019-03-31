<?php

namespace AuthtersTest\Chronicle\Integration;

use Authters\Chronicle\Support\Projection\ReadModel\ConnectionReadModel;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ConnectionReadModelIntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function it_up_and_down_read_model(): void
    {
        $manager = $this->newDatabaseInstance();
        $connection = $manager->getConnection();
        $readModel = $this->newReadModelInstance($connection);

        $this->assertFalse($readModel->isInitialized());

        if (!$readModel->isInitialized()) {
            $readModel->init();
        }

        $this->assertTrue($readModel->isInitialized());

        $readModel->delete();

        $this->assertFalse($readModel->isInitialized());
    }

    /**
     * @test
     */
    public function it_insert_data(): void
    {
        $manager = $this->newDatabaseInstance();
        $connection = $manager->getConnection();
        $readModel = $this->newReadModelInstance($connection);

        $this->assertFalse($readModel->isInitialized());

        if (!$readModel->isInitialized()) {
            $readModel->init();
        }

        $this->assertTrue($readModel->isInitialized());

        $readModel->stack('insert',[
            'id' => 1,
            'name' => 'baz'
        ]);

        $readModel->persist();

        $result = $connection->table('foo')->pluck('name')->first();
        $this->assertEquals('baz', $result);

        $readModel->delete();
    }

    /**
     * @test
     */
    public function it_update_data(): void
    {
        $manager = $this->newDatabaseInstance();
        $connection = $manager->getConnection();
        $readModel = $this->newReadModelInstance($connection);

        $this->assertFalse($readModel->isInitialized());

        if (!$readModel->isInitialized()) {
            $readModel->init();
        }

        $this->assertTrue($readModel->isInitialized());

        $readModel->stack('insert',[
            'id' => 1,
            'name' => 'baz'
        ]);

        $readModel->persist();

        $result = $connection->table('foo')->pluck('name')->first();
        $this->assertEquals('baz', $result);

        $readModel->stack('update', 1, ['name' => 'foo_bar']);
        $readModel->persist();

        $result = $connection->table('foo')->pluck('name')->first();
        $this->assertEquals('foo_bar', $result);

        $readModel->delete();
    }

    /**
     * @test
     */
    public function it_reset_read_model(): void
    {
        $manager = $this->newDatabaseInstance();
        $connection = $manager->getConnection();
        $readModel = $this->newReadModelInstance($connection);

        $this->assertFalse($readModel->isInitialized());

        if (!$readModel->isInitialized()) {
            $readModel->init();
        }

        $this->assertTrue($readModel->isInitialized());

        $connection->table('foo')->insert([
            'id' => 1, 'name' => 'baz'
        ]);

        $result = $connection->table('foo')->pluck('name')->first();
        $this->assertEquals('baz', $result);

        $readModel->reset();

        $this->assertEmpty($connection->table('foo')->get()->toArray());

        $readModel->delete();
    }

    private function newDatabaseInstance()
    {
        $db = new Manager();
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        return $db;
    }

    private function newReadModelInstance($connection): ConnectionReadModel
    {
        return new class($connection) extends ConnectionReadModel
        {
            public function reset(): void
            {
                parent::delete();

                parent::init();
            }

            protected function tableName(): string
            {
                return 'foo';
            }

            protected function up(): callable
            {
                return function (Blueprint $table) {
                    $table->integer('id');
                    $table->string('name');
                };
            }
        };
    }
}