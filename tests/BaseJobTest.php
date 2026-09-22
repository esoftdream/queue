<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\BaseJob;
use Esoftdream\Queue\Tests\Support\Jobs\TestJob;
use Esoftdream\Queue\Tests\Support\TestCase;

class BaseJobTest extends TestCase
{
    public function test_get_retry_after(): void
    {
        $job = new class(['data' => 'value']) extends BaseJob
        {
            public function process(): void {}
        };

        $this->assertSame(60, $job->getRetryAfter());
    }

    public function test_get_tries(): void
    {
        $job = new class(['data' => 'value']) extends BaseJob
        {
            public function process(): void {}
        };

        $this->assertSame(1, $job->getTries());
    }

    public function test_get_data(): void
    {
        $data = ['key' => 'value'];
        $job = new class($data) extends BaseJob
        {
            public function process(): void {}
        };

        $this->assertSame($data, $job->getData());
    }

    public function test_set_data(): void
    {
        $job = new class([]) extends BaseJob
        {
            public function process(): void {}
        };

        $job->setData(['new' => 'data']);

        $this->assertSame(['new' => 'data'], $job->getData());
    }

    public function test_execute_calls_process(): void
    {
        $job = new TestJob(['test' => 'data']);

        $this->assertFalse($job->processed);

        $job->execute();

        $this->assertTrue($job->processed);
        $this->assertSame(['test' => 'data'], $job->handledData);
    }

    public function test_constructor_sets_data(): void
    {
        $data = ['foo' => 'bar'];
        $job = new class($data) extends BaseJob
        {
            public function process(): void {}
        };

        $this->assertSame($data, $job->getData());
    }

    public function test_default_data_is_empty_array(): void
    {
        $job = new class extends BaseJob
        {
            public function process(): void {}
        };

        $this->assertSame([], $job->getData());
    }
}
