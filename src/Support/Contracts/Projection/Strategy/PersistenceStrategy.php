<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Strategy;

use Authters\Chronicle\Stream\StreamName;


interface PersistenceStrategy
{
    public function up(string $tableName): callable;

    public function columnNames(): array;

    public function prepareData(\Iterator $streamEvents): array;

    public function generateTableName(StreamName $streamName): string;
}