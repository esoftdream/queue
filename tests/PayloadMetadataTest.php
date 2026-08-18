<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\PayloadMetadata;
use Esoftdream\Queue\Payloads\Payload;
use Esoftdream\Queue\Payloads\PayloadCollection;
use Esoftdream\Queue\Tests\Support\TestCase;

class PayloadMetadataTest extends TestCase
{
    public function test_constructor_sets_data(): void
    {
        $data = ['key' => 'value'];
        $metadata = new PayloadMetadata($data);

        $this->assertEquals($data, $metadata->toArray());
    }

    public function test_set_chained_jobs(): void
    {
        $metadata = new PayloadMetadata;
        $collection = new PayloadCollection;
        $collection->add(new Payload('Job1'));
        $collection->add(new Payload('Job2'));

        $metadata->setChainedJobs($collection);

        $this->assertSame($collection, $metadata->getChainedJobs());
        $this->assertTrue($metadata->hasChainedJobs());
    }

    public function test_set_chained_jobs_to_null(): void
    {
        $metadata = new PayloadMetadata;
        $metadata->setChainedJobs(null);

        $this->assertNull($metadata->getChainedJobs());
        $this->assertFalse($metadata->hasChainedJobs());
    }

    public function test_has_chained_jobs_when_empty(): void
    {
        $metadata = new PayloadMetadata;
        $collection = new PayloadCollection;

        $metadata->setChainedJobs($collection);

        $this->assertFalse($metadata->hasChainedJobs());
    }

    public function test_set_and_get(): void
    {
        $metadata = new PayloadMetadata;

        $metadata->set('queue', 'high');
        $metadata->set('priority', 10);

        $this->assertEquals('high', $metadata->get('queue'));
        $this->assertEquals(10, $metadata->get('priority'));
    }

    public function test_get_with_default(): void
    {
        $metadata = new PayloadMetadata;

        $result = $metadata->get('nonexistent', 'default-value');

        $this->assertEquals('default-value', $result);
    }

    public function test_has(): void
    {
        $metadata = new PayloadMetadata;
        $metadata->set('key', 'value');

        $this->assertTrue($metadata->has('key'));
        $this->assertFalse($metadata->has('other'));
    }

    public function test_remove(): void
    {
        $metadata = new PayloadMetadata;
        $metadata->set('key', 'value');
        $metadata->remove('key');

        $this->assertFalse($metadata->has('key'));
        $this->assertNull($metadata->get('key'));
    }

    public function test_to_array(): void
    {
        $metadata = new PayloadMetadata(['a' => 1, 'b' => 2]);

        $this->assertEquals(['a' => 1, 'b' => 2], $metadata->toArray());
    }

    public function test_json_serialize(): void
    {
        $metadata = new PayloadMetadata(['x' => 'y']);
        $json = json_encode($metadata);

        $this->assertEquals('{"x":"y"}', $json);
    }

    public function test_from_array_with_chained_jobs(): void
    {
        $data = [
            'queue' => 'default',
            'chainedJobs' => [
                ['job' => 'Job1', 'data' => []],
                ['job' => 'Job2', 'data' => ['id' => 1]],
            ],
        ];

        $metadata = PayloadMetadata::fromArray($data);

        $this->assertEquals('default', $metadata->get('queue'));
        $this->assertTrue($metadata->hasChainedJobs());
        $this->assertEquals(2, $metadata->getChainedJobs()->count());
    }

    public function test_from_array_without_chained_jobs(): void
    {
        $data = ['key' => 'value'];
        $metadata = PayloadMetadata::fromArray($data);

        $this->assertEquals('value', $metadata->get('key'));
        $this->assertFalse($metadata->hasChainedJobs());
    }
}
