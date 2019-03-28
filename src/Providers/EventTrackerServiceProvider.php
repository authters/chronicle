<?php

namespace Authters\Chronicle\Providers;

use Authters\Chronicle\Chronicler\Events\AppendToEvent;
use Authters\Chronicle\Chronicler\Events\BusChronicler\OnAppendToEventDispatch;
use Authters\Chronicle\Chronicler\Events\BusChronicler\OnCommitEventDispatch;
use Authters\Chronicle\Chronicler\Events\BusChronicler\OnCreateEventDispatch;
use Authters\Chronicle\Chronicler\Events\BusChronicler\OnRollbackEventDispatch;
use Authters\Chronicle\Chronicler\Events\CreateEvent;
use Authters\Chronicle\Chronicler\Events\DeleteEvent;
use Authters\Chronicle\Chronicler\Events\FetchCategoryNamesEvent;
use Authters\Chronicle\Chronicler\Events\FetchStreamNamesEvent;
use Authters\Chronicle\Chronicler\Events\HasStreamEvent;
use Authters\Chronicle\Chronicler\Events\LoadEvent;
use Authters\Chronicle\Chronicler\Events\LoadReverseEvent;
use Authters\Chronicle\Chronicler\Events\Streams\OnAppendToStream;
use Authters\Chronicle\Chronicler\Events\Streams\OnCreateStream;
use Authters\Chronicle\Chronicler\Events\Streams\OnDeleteStream;
use Authters\Chronicle\Chronicler\Events\Streams\OnFetchCategoryNames;
use Authters\Chronicle\Chronicler\Events\Streams\OnfetchStreamNames;
use Authters\Chronicle\Chronicler\Events\Streams\OnHasStream;
use Authters\Chronicle\Chronicler\Events\Streams\OnLoadReverseStream;
use Authters\Chronicle\Chronicler\Events\Streams\OnLoadStream;
use Authters\Chronicle\Chronicler\Events\Streams\OnUpdateStreamMetadata;
use Authters\Chronicle\Chronicler\Events\Transaction\BeginTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\CommitTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\OnBeginTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\OnCommitTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\OnRollbackTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\RollbackTransaction;
use Authters\Chronicle\Chronicler\Events\UpdateStreamMetadataEvent;
use Authters\Chronicle\Chronicler\Tracker\TransactionalEventTracker;
use Authters\Tracker\Contract\Tracker;
use Illuminate\Support\ServiceProvider;

class EventTrackerServiceProvider extends ServiceProvider
{
    // fixMe transactional events

    public function register(): void
    {
        $eventChronicler = config('chronicle.chronicler.tracker');
        if (!$eventChronicler) {
            throw new \RuntimeException("Event tracker class name not found in chronicle config");
        }

        if (!$tracker = $eventChronicler['concrete'] ?? null) {
            throw new \RuntimeException("Invalid config");
        }

        $transactionalTracker = $eventChronicler['transactional_concrete'] ?? null;

        $this->app->singleton($tracker, function () use ($transactionalTracker, $tracker) {
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

        FetchStreamNamesEvent::class,
        FetchCategoryNamesEvent::class,
        UpdateStreamMetadataEvent::class,

        BeginTransaction::class,
        CommitTransaction::class,
        RollbackTransaction::class,
    ];

    /**
     * @var array
     */
    protected $eventSubscribers = [
        OnAppendToStream::class,
        OnCreateStream::class,
        OnDeleteStream::class,
        OnLoadStream::class,
        OnLoadReverseStream::class,
        OnHasStream::class,

        OnfetchStreamNames::class,
        OnFetchCategoryNames::class,
        OnUpdateStreamMetadata::class,

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