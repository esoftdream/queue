<?php

namespace Esoftdream\Queue\Tests\Message;

use Esoftdream\Queue\Message\SymfonyQueueMessage;
use Esoftdream\Queue\Tests\Support\TestCase;

class SymfonyQueueMessageTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $message = new SymfonyQueueMessage('TestJob', ['key' => 'value'], ['queue' => 'high']);

        $this->assertEquals('TestJob', $message->job);
        $this->assertEquals(['key' => 'value'], $message->data);
        $this->assertEquals(['queue' => 'high'], $message->metadata);
    }

    public function testGetQueueFromMetadata(): void
    {
        $message = new SymfonyQueueMessage('Job', [], ['queue' => 'my-queue']);

        $this->assertEquals('my-queue', $message->getQueue());
    }

    public function testGetQueueDefaultsToDefault(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertEquals('default', $message->getQueue());
    }

    public function testGetPriority(): void
    {
        $message = new SymfonyQueueMessage('Job', [], ['priority' => 5]);

        $this->assertEquals(5, $message->getPriority());
    }

    public function testGetPriorityDefaultsToZero(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertEquals(0, $message->getPriority());
    }

    public function testGetAttempts(): void
    {
        $message = new SymfonyQueueMessage('Job', [], ['attempts' => 3]);

        $this->assertEquals(3, $message->getAttempts());
    }

    public function testGetAttemptsDefaultsToZero(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertEquals(0, $message->getAttempts());
    }

    public function testEmptyMetadata(): void
    {
        $message = new SymfonyQueueMessage('Job', []);

        $this->assertEquals('default', $message->getQueue());
        $this->assertEquals(0, $message->getPriority());
        $this->assertEquals(0, $message->getAttempts());
    }
}
