<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\QueuePushResult;
use Esoftdream\Queue\Tests\Support\TestCase;

class QueuePushResultTest extends TestCase
{
    public function test_success_creates_result_with_job_id(): void
    {
        $result = QueuePushResult::success('job-123');

        $this->assertTrue($result->isSuccess);
        $this->assertEquals('job-123', $result->jobId);
        $this->assertNull($result->error);
        $this->assertFalse($result->isFailed());
    }

    public function test_failure_creates_result_with_error(): void
    {
        $result = QueuePushResult::failure('Connection failed');

        $this->assertFalse($result->isSuccess);
        $this->assertNull($result->jobId);
        $this->assertEquals('Connection failed', $result->error);
        $this->assertTrue($result->isFailed());
    }

    public function test_success_with_integer_job_id(): void
    {
        $result = QueuePushResult::success(456);

        $this->assertTrue($result->isSuccess);
        $this->assertEquals('456', $result->jobId);
    }

    public function test_is_failed_returns_correct_status(): void
    {
        $success = QueuePushResult::success('job-1');
        $failure = QueuePushResult::failure('error');

        $this->assertFalse($success->isFailed());
        $this->assertTrue($failure->isFailed());
    }
}
