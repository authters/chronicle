<?php

namespace Authters\Chronicle\Support\Projection\ReadModel;

use Illuminate\Database\Connection;

abstract class ConnectionReadModel extends AbstractReadModel
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function init(): void
    {
        $this->connection->getSchemaBuilder()->create($this->tableName(), $this->up());
    }

    public function isInitialized(): bool
    {
        return $this->connection->getSchemaBuilder()->hasTable($this->tableName());
    }

    public function reset(): void
    {
        $schema = $this->connection->getSchemaBuilder();

        $schema->disableForeignKeyConstraints();

        $this->connection->table($this->tableName())->truncate();

        $schema->enableForeignKeyConstraints();
    }

    public function delete(): void
    {
        $schema = $this->connection->getSchemaBuilder();

        $schema->disableForeignKeyConstraints();

        $schema->drop($this->tableName());

        $schema->enableForeignKeyConstraints();
    }

    protected function insert(array $data): void
    {
        $this->connection->table($this->tableName())->insert($data);
    }

    protected function update(string $id, array $data): void
    {
        $this->connection->table($this->tableName())->where($this->getKey(), $id)->update($data);
    }

    public function getKey(): string
    {
        return 'id';
    }

    abstract protected function tableName(): string;

    abstract protected function up(): callable;
}