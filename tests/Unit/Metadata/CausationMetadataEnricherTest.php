<?php

namespace AuthtersTest\Chronicle\Unit\Metadata;

use Authters\Chronicle\Metadata\Causation\CausationMetadataEnricher;
use AuthtersTest\Chronicle\Unit\TestCase;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\PayloadTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CausationMetadataEnricherTest extends TestCase
{
    /**
     * @test
     */
    public function it_enrich_message(): void
    {
        $command = $this->someCommandInstance();
        $this->assertEmpty($command->metadata());

        $enricher = new CausationMetadataEnricher($command);

        $event = $this->someEventInstance();
        $enrichedEvent = $enricher->enrich($event);

        $this->assertEquals(
            [
                "_causation_id" => "8d43f983-52ea-4e68-99a4-8d90fc9497a8",
                "_causation_name" => "some_command"
            ], $enrichedEvent->metadata()
        );
    }

    /**
     * @test
     */
    public function it_reset_command(): void
    {
        $command = $this->someCommandInstance();
        $enricher = new CausationMetadataEnricher($command);

        $this->assertEquals($command, $enricher->getCommand());

        $enricher->reset();

        $this->assertNull($enricher->getCommand());
    }

    private function someCommandInstance(): Command
    {
        return new class() extends Command
        {
            use PayloadTrait;

            public function messageName(): string
            {
                return 'some_command';
            }

            public function uuid(): UuidInterface
            {
                return Uuid::fromString('8d43f983-52ea-4e68-99a4-8d90fc9497a8');
            }
        };
    }

    private function someEventInstance(): DomainEvent
    {
        return new class extends DomainEvent
        {
            use PayloadTrait;
        };
    }
}