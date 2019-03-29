<?php

namespace Authters\Chronicle\Support\Console\Standard;

use Authters\Chronicle\Support\Projection\InternalProjectionName;
use Exception;

class CategoryStreamProjectionRunner extends StreamProjectionRunner
{
    /**
     * @var string
     */
    protected $signature = 'projector:category-runner {--keep_running= : Run process in background}';

    /**
     * @var string
     */
    protected $description = 'Category runner projector';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->manager
            ->createProjection('$by_category')
            ->fromAll()
            ->whenAny(function (array $state, Message $event): void {
                $category = InternalProjectionName::fromCategory(
                    $this->streamName()
                );
                if ($category->isValid()) {
                    $this->linkTo($category, $event);
                }
            })
            ->run($this->keepRunning());
    }
}