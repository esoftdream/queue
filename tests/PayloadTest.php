<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\Payloads\Payload;
use Esoftdream\Queue\Tests\Support\TestCase;

class PayloadTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $data = ['key' => 'value'];
        $metadata = ['queue' => 'high'];
        $payload = new Payload('TestJob', $data, $metadata);

        $this->assertSame('TestJob', $payload->job);
        $this->assertSame($data, $payload->data);
        $this->assertSame($metadata, $payload->metadata);
    }

    public function test_from_array_creates_payload(): void
    {
        $data = [
            'job' => 'MyJob',
            'data' => ['id' => 1],
            'metadata' => ['priority' => 10],
        ];

        $payload = Payload::fromArray($data);

        $this->assertSame('MyJob', $payload->job);
        $this->assertSame(['id' => 1], $payload->data);
        $this->assertSame(['priority' => 10], $payload->metadata);
    }

    public function test_from_array_with_missing_keys(): void
    {
        $payload = Payload::fromArray([]);

        $this->assertSame('', $payload->job);
        $this->assertSame([], $payload->data);
        $this->assertNull($payload->metadata);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $payload = new Payload('JobClass', ['foo' => 'bar'], ['queue' => 'default']);
        $array = $payload->toArray();

        $this->assertSame([
            'job' => 'JobClass',
            'data' => ['foo' => 'bar'],
            'metadata' => ['queue' => 'default'],
        ], $array);
    }

    public function test_get_job(): void
    {
        $payload = new Payload('TestJob');
        $this->assertSame('TestJob', $payload->getJob());
    }

    public function test_get_data(): void
    {
        $payload = new Payload('Job', ['a' => 1, 'b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], $payload->getData());
    }

    public function test_get_metadata(): void
    {
        $metadata = ['key' => 'value'];
        $payload = new Payload('Job', [], $metadata);
        $this->assertSame($metadata, $payload->getMetadata());
    }

    public function test_get_metadata_returns_null_when_not_set(): void
    {
        $payload = new Payload('Job');
        $this->assertNull($payload->getMetadata());
    }
}
