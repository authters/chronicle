<?php

namespace Authters\Chronicle\Support\Console;

use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CreateEventStreamsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'chronicle:create-streams 
                                {streams : One or many streams separated by coma}';

    /**
     * @var string
     */
    protected $description = 'Create stream(s)';

    /**
     * @var Chronicler
     */
    private $publisher;

    public function __construct(Chronicler $publisher)
    {
        parent::__construct();

        $this->publisher = $publisher;
    }

    public function handle(): void
    {
        $this->getStreams()->each(function (Stream $stream) {
            $this->publisher->create($stream);

            $this->line("Stream name {$stream->streamName()} created");
        });

        $this->info('Streams created');
    }

    protected function getStreams(): Collection
    {
        $streams = $this->argument('streams');

        if (!Str::contains(',', $streams)) {
            $streams .= ',';
        }

        return collect(explode(',', $streams))
            ->filter()
            ->transform(function (string $name) {
                return new Stream(new StreamName($name), new \ArrayIterator());
            });
    }
}