<?php

return [

    'connections' => [

        'chronicler' => [

            'mysql' => [
                'chronicler' => \Authters\Chronicle\Chronicler\Connection\ConnectionChronicler::class,
                'persistence_strategy' => \Authters\Chronicle\Projection\Strategy\MysqlSingleStreamStrategy::class,
                'naming_strategy' => \Authters\Chronicle\Projection\Strategy\SingleStreamNamingStrategy::class,
                'metadata_matchers' => \Authters\Chronicle\Support\Metadata\ConnectionMetadataMatchers::class
            ],
        ],

        'snapshot' => [

        ]
    ],

    'chronicling' => [

        'default' => 'mysql',

        'aggregate_repositories' => [

            'service_key' => [
                'concrete' => '', // or [abstract,concrete]
                'type' => '',
                'translator' => '',
                'stream_name' => 'user_stream',
                'metadata' => [],
                'snapshot' => '',
            ]
        ]
    ],

    'chronicler' => [

        'default' => 'mysql',

        'use_transaction' => true,

        'batch_size' => 10000,

        'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,

        'decorator' => [
            'event_chronicler' => \Authters\Chronicle\Chronicler\DefaultEventChronicler::class,
            'transactional_chronicler' => \Authters\Chronicle\Chronicler\TransactionalDefaultEventChronicler::class,
        ],

        'providers' => [
            'event_stream' => \Authters\Chronicle\Projection\Model\EventStream::class,
            'projection' => \Authters\Chronicle\Projection\Model\Projection::class,
        ],

        'tracker' => [
            'concrete' => \Authters\Chronicle\Chronicler\Tracker\EventTracker::class,
            'transactional_concrete' => \Authters\Chronicle\Chronicler\Tracker\TransactionalEventTracker::class
        ]
    ],

    'projection' => [

        'only_for_console' => false,

        'manager' => \Authters\Chronicle\Projection\ProjectorManager::class,

        'commands' => [
            \Authters\Chronicle\Support\Console\CreateEventStreamsCommand::class,
            \Authters\Chronicle\Support\Console\ProjectorFinderCommand::class
        ]
    ]
];