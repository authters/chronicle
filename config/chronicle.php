<?php

return [
    'connections' => [

        'publisher' => [

            'mysql' => [
                'publisher' => \Authters\Chronicle\Publisher\Connection\ConnectionPublisher::class,
                'persistence_strategy' => \Authters\Chronicle\Projection\Strategy\MysqlSingleStreamStrategy::class,
                'naming_strategy' => \Authters\Chronicle\Projection\Strategy\SingleStreamNamingStrategy::class,
                'metadata_matchers' => \Authters\Chronicle\Support\Metadata\ConnectionMetadataMatchers::class
            ],
        ],

        'snapshot' => [

        ]
    ],

    'publishing' => [

        'default' => 'mysql',

        'aggregate_repositories' => [

            'service_key' => [
                //'connections' => 'mysql',
                'concrete' => '', // or [abstract,concrete]
                'type' => '',
                'translator' => '',
                'stream_name' => 'user_stream',
                'metadata' => [],
                'snapshot' => '',
            ]
        ]
    ],

    'publisher' => [

        'use_transaction' => true,

        'batch_size' => 10000,

        'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,

        'decorator' => [
            'event_publisher' => \Authters\Chronicle\Publisher\DefaultEventPublisher::class,
            'transactional_publisher' => \Authters\Chronicle\Publisher\TransactionalDefaultEventPublisher::class,
        ],

        'providers' => [
            'event_stream' => \Authters\Chronicle\Projection\Model\EventStream::class,
            'projection' => \Authters\Chronicle\Projection\Model\Projection::class,
        ],

        'tracker' => [
            'concrete' => \Authters\Chronicle\Publisher\Tracker\EventTracker::class,
            'transactional_concrete' => \Authters\Chronicle\Publisher\Tracker\TransactionalEventTracker::class
        ]
    ]
];