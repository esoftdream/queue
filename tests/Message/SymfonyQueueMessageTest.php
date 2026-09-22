<?php

namespace Esoftdream\Queue\Tests\Message;

use Esoftdream\Queue\Message\SymfonyQueueMessage;
use Esoftdream\Queue\Tests\Support\TestCase;

class SymfonyQueueMessageTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $message = new SymfonyQueueMessage('TestJob', ['key' => 'value'], ['queue' => 'high']);

        $this->assertSame('TestJob', $message->job);
        $this->assertSame(['key' => 'value'], $message->data);
        $this->assertSame(['queue' => 'high'], $message->metadata);
    }

    public function test_get_queue_from_metadata(): void
    {
        $message = new SymfonyQueueMessage('Job', [], ['queue' => 'my-queue']);

        $this->assertSame('my-queue', $message->getQueue());
    }

    public function test_get_queue_defaults_to_default(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertSame('default', $message->getQueue());
    }

    public function test_get_priority(): void
    {
        $message = new SymfonyQueueMessage('Job', [], ['priority' => 5]);

        $this->assertSame(5, $message->getPriority());
    }

    public function test_get_priority_defaults_to_zero(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertSame(0, $message->getPriority());
    }

    public function test_get_attempts(): void
    {
        $message = new SymfonyQueueMessage('Job', [], ['attempts' => 3]);

        $this->assertSame(3, $message->getAttempts());
    }

    public function test_get_attempts_defaults_to_zero(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertSame(0, $message->getAttempts());
    }

    public function test_empty_metadata(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertSame('default', $message->getQueue());
        $this->assertSame(0, $message->getPriority());
        $this->assertSame(0, $message->getAttempts());
    }
}
