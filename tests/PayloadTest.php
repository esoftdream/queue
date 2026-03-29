<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\Payloads\Payload;
use Esoftdream\Queue\Tests\Support\TestCase;

class PayloadTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $data = ['key' => 'value'];
        $metadata = ['queue' => 'high'];
        $payload = new Payload('TestJob', $data, $metadata);

        $this->assertEquals('TestJob', $payload->job);
        $this->assertEquals($data, $payload->data);
        $this->assertEquals($metadata, $payload->metadata);
    }

    public function testFromArrayCreatesPayload(): void
    {
        $data = [
            'job' => 'MyJob',
            'data' => ['id' => 1],
            'metadata' => ['priority' => 10],
        ];

        $payload = Payload::fromArray($data);

        $this->assertEquals('MyJob', $payload->job);
        $this->assertEquals(['id' => 1], $payload->data);
        $this->assertEquals(['priority' => 10], $payload->metadata);
    }

    public function testFromArrayWithMissingKeys(): void
    {
        $payload = Payload::fromArray([]);

        $this->assertEquals('', $payload->job);
        $this->assertEquals([], $payload->data);
        $this->assertNull($payload->metadata);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $payload = new Payload('JobClass', ['foo' => 'bar'], ['queue' => 'default']);
        $array = $payload->toArray();

        $this->assertEquals([
            'job' => 'JobClass',
            'data' => ['foo' => 'bar'],
            'metadata' => ['queue' => 'default'],
        ], $array);
    }

    public function testGetJob(): void
    {
        $payload = new Payload('TestJob');
        $this->assertEquals('TestJob', $payload->getJob());
    }

    public function testGetData(): void
    {
        $payload = new Payload('Job', ['a' => 1, 'b' => 2]);
        $this->assertEquals(['a' => 1, 'b' => 2], $payload->getData());
    }

    public function testGetMetadata(): void
    {
        $metadata = ['key' => 'value'];
        $payload = new Payload('Job', [], $metadata);
        $this->assertEquals($metadata, $payload->getMetadata());
    }

    public function testGetMetadataReturnsNullWhenNotSet(): void
    {
        $payload = new Payload('Job');
        $this->assertNull($payload->getMetadata());
    }
}
