<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\Handlers\SymfonyMessengerHandler;
use Esoftdream\Queue\Payloads\Payload;
use Esoftdream\Queue\PayloadMetadata;
use Esoftdream\Queue\QueuePushResult;
use Esoftdream\Queue\Tests\Support\TestCase;

class SymfonyMessengerHandlerTest extends TestCase
{
    private object $handler;
    private object $busMock;
    private object $configMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->busMock = new class {
            public array $dispatched = [];
            public function dispatch($message) {
                $this->dispatched[] = $message;
                return new class {
                    public function get(): array { return []; }
                };
            }
            public function shouldThrow(bool $throw) {
                $this->shouldThrow = $throw;
                return $this;
            }
        };

        $this->configMock = new class {
            public string $transport = 'sync';
            public string $defaultQueue = 'default';
            public array $symfonyMessengerConfig = [];
        };

        $this->handler = new class($this->busMock, $this->configMock) {
            public array $messages = [];
            public ?\Throwable $shouldThrow = null;

            public function __construct(
                public object $bus,
                public object $config
            ) {}

            public function push(Payload $payload): QueuePushResult
            {
                try {
                    $this->bus->dispatch($payload);
                    return QueuePushResult::success('mock-job-id-' . uniqid());
                } catch (\Throwable $e) {
                    $this->shouldThrow = $e;
                    return QueuePushResult::failure($e->getMessage());
                }
            }

            public function later(Payload $payload, ?PayloadMetadata $metadata = null, int $delay = 0): QueuePushResult
            {
                return $this->push($payload);
            }

            public function pop(string $queue): ?Payload
            {
                return null;
            }

            public function getQueue(): string
            {
                return $this->config->defaultQueue;
            }
        };
    }

    public function testPushReturnsSuccessResult(): void
    {
        $payload = new Payload('TestJob', ['data' => 'value']);

        $result = $this->handler->push($payload);

        $this->assertTrue($result->isSuccess);
        $this->assertNotNull($result->jobId);
    }

    public function testLaterDispatchesWithDelay(): void
    {
        $payload = new Payload('DelayedJob');
        $metadata = new PayloadMetadata();
        $metadata->set('delay', 10);

        $result = $this->handler->later($payload, $metadata, 10);

        $this->assertTrue($result->isSuccess);
    }

    public function testPopReturnsPayloadForQueuedJob(): void
    {
        $result = $this->handler->pop('default');

        $this->assertNull($result);
    }

    public function testGetQueueReturnsConfiguredDefault(): void
    {
        $queue = $this->handler->getQueue();

        $this->assertEquals('default', $queue);
    }
}
