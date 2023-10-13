<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\QueuePushResult;
use Esoftdream\Queue\Tests\Support\TestCase;

class QueuePushResultTest extends TestCase
{
    public function testSuccessCreatesResultWithJobId(): void
    {
        $result = QueuePushResult::success('job-123');

        $this->assertTrue($result->isSuccess);
        $this->assertEquals('job-123', $result->jobId);
        $this->assertNull($result->error);
        $this->assertFalse($result->isFailed());
    }

    public function testFailureCreatesResultWithError(): void
    {
        $result = QueuePushResult::failure('Connection failed');

        $this->assertFalse($result->isSuccess);
        $this->assertNull($result->jobId);
        $this->assertEquals('Connection failed', $result->error);
        $this->assertTrue($result->isFailed());
    }

    public function testSuccessWithIntegerJobId(): void
    {
        $result = QueuePushResult::success(456);

        $this->assertTrue($result->isSuccess);
        $this->assertEquals('456', $result->jobId);
    }

    public function testIsFailedReturnsCorrectStatus(): void
    {
        $success = QueuePushResult::success('job-1');
        $failure = QueuePushResult::failure('error');

        $this->assertFalse($success->isFailed());
        $this->assertTrue($failure->isFailed());
    }
}
