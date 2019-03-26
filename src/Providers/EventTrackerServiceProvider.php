<?php

namespace Authters\Chronicle\Providers;

use Authters\Chronicle\Publisher\Events\AppendToEvent;
use Authters\Chronicle\Publisher\Events\BusPublisher\OnAppendToEventDispatch;
use Authters\Chronicle\Publisher\Events\BusPublisher\OnCommitEventDispatch;
use Authters\Chronicle\Publisher\Events\BusPublisher\OnCreateEventDispatch;
use Authters\Chronicle\Publisher\Events\BusPublisher\OnRollbackEventDispatch;
use Authters\Chronicle\Publisher\Events\CreateEvent;
use Authters\Chronicle\Publisher\Events\DeleteEvent;
use Authters\Chronicle\Publisher\Events\HasStreamEvent;
use Authters\Chronicle\Publisher\Events\LoadEvent;
use Authters\Chronicle\Publisher\Events\LoadReverseEvent;
use Authters\Chronicle\Publisher\Events\Streams\OnAppendToStreamPublisher;
use Authters\Chronicle\Publisher\Events\Streams\OnCreateStreamPublisher;
use Authters\Chronicle\Publisher\Events\Streams\OnDeleteStreamPublisher;
use Authters\Chronicle\Publisher\Events\Streams\OnHasStreamPublisher;
use Authters\Chronicle\Publisher\Events\Streams\OnLoadReverseStreamPublisher;
use Authters\Chronicle\Publisher\Events\Streams\OnLoadStreamPublisher;
use Authters\Chronicle\Publisher\Events\Transaction\BeginTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\CommitTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\OnBeginTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\OnCommitTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\OnRollbackTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\RollbackTransaction;
use Authters\Chronicle\Publisher\Tracker\TransactionalEventTracker;
use Authters\Tracker\Contract\Tracker;
use Illuminate\Support\ServiceProvider;

class EventTrackerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $eventPublisher = config('chronicle.publisher.tracker');
        if (!$eventPublisher) {
            return;
        }

        if (!$tracker = $eventPublisher['concrete'] ?? null) {
            throw new \RuntimeException("Invalid config");
        }

        $transactionalTracker = $eventPublisher['transactional_tracker'] ?? null;

        $this->app->bind($tracker, function () use ($transactionalTracker, $tracker) {
            $instance = new $transactionalTracker ?? $tracker;

            $this->attachEventsToTracker($instance);

            return $instance;
        });
    }

    protected function attachEventsToTracker(Tracker $tracker): void
    {
        $events = array_merge($this->events, $this->eventSubscribers);

        if ($tracker instanceof TransactionalEventTracker) {
            $events = array_merge($events, $this->transactionalEventSubscribers);
        }

        foreach ($events as $event) {
            $tracker->subscribe($this->app->get($event));
        }
    }


    /**
     * @var array
     */
    protected $events = [
        AppendToEvent::class,
        CreateEvent::class,
        DeleteEvent::class,
        LoadEvent::class,
        LoadReverseEvent::class,
        HasStreamEvent::class,
        BeginTransaction::class,
        CommitTransaction::class,
        RollbackTransaction::class,
    ];

    /**
     * @var array
     */
    protected $eventSubscribers = [
        OnAppendToStreamPublisher::class,
        OnCreateStreamPublisher::class,
        OnDeleteStreamPublisher::class,
        OnLoadStreamPublisher::class,
        OnLoadReverseStreamPublisher::class,
        OnHasStreamPublisher::class,
        OnBeginTransaction::class,
        OnCommitTransaction::class,
        OnRollbackTransaction::class,
    ];

    protected $transactionalEventSubscribers = [
        OnAppendToEventDispatch::class,
        OnCommitEventDispatch::class,
        OnCreateEventDispatch::class,
        OnRollbackEventDispatch::class
    ];
}