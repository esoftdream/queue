<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\BaseJob;
use Esoftdream\Queue\Tests\Support\TestCase;
use Esoftdream\Queue\Tests\Support\Jobs\TestJob;

class BaseJobTest extends TestCase
{
    public function testGetRetryAfter(): void
    {
        $job = new class(['data' => 'value']) extends BaseJob {
            public function process(): void {}
        };

        $this->assertEquals(60, $job->getRetryAfter());
    }

    public function testGetTries(): void
    {
        $job = new class(['data' => 'value']) extends BaseJob {
            public function process(): void {}
        };

        $this->assertEquals(1, $job->getTries());
    }

    public function testGetData(): void
    {
        $data = ['key' => 'value'];
        $job = new class($data) extends BaseJob {
            public function process(): void {}
        };

        $this->assertEquals($data, $job->getData());
    }

    public function testSetData(): void
    {
        $job = new class([]) extends BaseJob {
            public function process(): void {}
        };

        $job->setData(['new' => 'data']);

        $this->assertEquals(['new' => 'data'], $job->getData());
    }

    public function testExecuteCallsProcess(): void
    {
        $job = new TestJob(['test' => 'data']);

        $this->assertFalse($job->processed);

        $job->execute();

        $this->assertTrue($job->processed);
        $this->assertEquals(['test' => 'data'], $job->handledData);
    }

    public function testConstructorSetsData(): void
    {
        $data = ['foo' => 'bar'];
        $job = new class($data) extends BaseJob {
            public function process(): void {}
        };

        $this->assertEquals($data, $job->getData());
    }

    public function testDefaultDataIsEmptyArray(): void
    {
        $job = new class extends BaseJob {
            public function process(): void {}
        };

        $this->assertEquals([], $job->getData());
    }
}
