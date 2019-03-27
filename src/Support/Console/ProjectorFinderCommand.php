<?php

namespace Authters\Chronicle\Support\Console;

use Authters\Chronicle\Exceptions\ProjectionNotFound;
use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Illuminate\Console\Command;

class ProjectorFinderCommand extends Command
{
    protected $signature = 'chronicle:find 
                            {name : stream name}
                            {field : available field names (status, positions, state)}';

    protected $description = 'Find Status, stream positions or state by projection name';

    /**
     * @var ProjectionManager
     */
    private $projectionManager;


    public function __construct(ProjectionManager $projectionManager)
    {
        parent::__construct();

        $this->projectionManager = $projectionManager;
    }

    /**
     * @return array
     * @throws ProjectionNotFound
     */
    public function handle(): array
    {
        [$streamName, $fieldName] = $this->determineOptions();

        $result = $this->fetchProjectionByField($streamName, $fieldName);

        $this->warn('Printing result ...');

        foreach ($result as $line) {
            $this->line($line);
        }

        $this->info('Done.');

        return $result;
    }

    protected function fetchProjectionByField(string $streamName, string $fieldName): array
    {
        switch ($fieldName) {
            case 'state':
                return $this->projectionManager->stateOf($streamName);
            case  'positions':
                return $this->projectionManager->streamPositionsOf($streamName);
            case 'status':
                return [$this->projectionManager->statusOf($streamName)];
            default:
                throw new \InvalidArgumentException("invalid name $fieldName");
        }
    }

    protected function determineOptions(): array
    {
        $name = $this->argument('name') ?? null;
        if (!$name) {
            throw new \InvalidArgumentException("invalid name argument");
        }

        $field = $this->argument('field');

        if (!in_array($field, ['state', 'positions', 'status'])) {
            throw new \InvalidArgumentException("Invalid field option");
        }

        return [$name, $field];
    }
}