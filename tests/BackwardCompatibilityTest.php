<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\BaseJob;
use Esoftdream\Queue\Enums\Status;
use Esoftdream\Queue\Exceptions\QueueException;
use Esoftdream\Queue\Handlers\DatabaseHandler;
use Esoftdream\Queue\Handlers\SymfonyMessengerHandler;
use Esoftdream\Queue\Interfaces\JobInterface as LegacyJobInterface;
use Esoftdream\Queue\Interfaces\QueueInterface as LegacyQueueInterface;
use Esoftdream\Queue\JobInterface;
use Esoftdream\Queue\Message\MessageHandler;
use Esoftdream\Queue\QueueInterface;
use Esoftdream\Queue\Tests\Support\TestCase;

class BackwardCompatibilityTest extends TestCase
{
    public function test_legacy_job_interface(): void
    {
        $job = new class([]) extends BaseJob implements LegacyJobInterface
        {
            public function process(): void {}
        };

        $this->assertInstanceOf(JobInterface::class, $job);
        $this->assertInstanceOf(LegacyJobInterface::class, $job);
    }

    public function test_legacy_status_enum(): void
    {
        $this->assertSame('waiting', Status::Waiting->value);
        $this->assertSame('reserved', Status::Reserved->value);
        $this->assertSame('done', Status::Done->value);
        $this->assertSame('failed', Status::Failed->value);
        $this->assertSame('Waiting', Status::Waiting->label());
    }

    public function test_legacy_queue_exception(): void
    {
        $e1 = QueueException::forIncorrectHandler();
        $this->assertInstanceOf(QueueException::class, $e1);
        $this->assertSame('Incorrect handler name.', $e1->getMessage());

        $e2 = QueueException::forIncorrectJobHandler();
        $this->assertSame('Incorrect job handler name.', $e2->getMessage());

        $e3 = QueueException::forIncorrectQueue();
        $this->assertSame('Incorrect queue name.', $e3->getMessage());

        $e4 = QueueException::forIncorrectPriority();
        $this->assertSame('Incorrect priority value.', $e4->getMessage());

        $e5 = QueueException::forHandlerNotAvailable('redis');
        $this->assertSame("Handler 'redis' is not available.", $e5->getMessage());

        $e6 = QueueException::forJobFailed('timeout');
        $this->assertSame('Job failed: timeout', $e6->getMessage());
    }

    public function test_handlers_and_message_handler_exist(): void
    {
        $this->assertTrue(class_exists(DatabaseHandler::class));
        $this->assertTrue(class_exists(SymfonyMessengerHandler::class));
        $this->assertTrue(class_exists(MessageHandler::class));
        $this->assertTrue(interface_exists(QueueInterface::class));
        $this->assertTrue(interface_exists(LegacyQueueInterface::class));
    }
}
