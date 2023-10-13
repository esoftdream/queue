<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\PayloadMetadata;
use Esoftdream\Queue\Payloads\PayloadCollection;
use Esoftdream\Queue\Payloads\Payload;
use Esoftdream\Queue\Tests\Support\TestCase;

class PayloadMetadataTest extends TestCase
{
    public function testConstructorSetsData(): void
    {
        $data = ['key' => 'value'];
        $metadata = new PayloadMetadata($data);

        $this->assertEquals($data, $metadata->toArray());
    }

    public function testSetChainedJobs(): void
    {
        $metadata = new PayloadMetadata();
        $collection = new PayloadCollection();
        $collection->add(new Payload('Job1'));
        $collection->add(new Payload('Job2'));

        $metadata->setChainedJobs($collection);

        $this->assertSame($collection, $metadata->getChainedJobs());
        $this->assertTrue($metadata->hasChainedJobs());
    }

    public function testSetChainedJobsToNull(): void
    {
        $metadata = new PayloadMetadata();
        $metadata->setChainedJobs(null);

        $this->assertNull($metadata->getChainedJobs());
        $this->assertFalse($metadata->hasChainedJobs());
    }

    public function testHasChainedJobsWhenEmpty(): void
    {
        $metadata = new PayloadMetadata();
        $collection = new PayloadCollection();

        $metadata->setChainedJobs($collection);

        $this->assertFalse($metadata->hasChainedJobs());
    }

    public function testSetAndGet(): void
    {
        $metadata = new PayloadMetadata();

        $metadata->set('queue', 'high');
        $metadata->set('priority', 10);

        $this->assertEquals('high', $metadata->get('queue'));
        $this->assertEquals(10, $metadata->get('priority'));
    }

    public function testGetWithDefault(): void
    {
        $metadata = new PayloadMetadata();

        $result = $metadata->get('nonexistent', 'default-value');

        $this->assertEquals('default-value', $result);
    }

    public function testHas(): void
    {
        $metadata = new PayloadMetadata();
        $metadata->set('key', 'value');

        $this->assertTrue($metadata->has('key'));
        $this->assertFalse($metadata->has('other'));
    }

    public function testRemove(): void
    {
        $metadata = new PayloadMetadata();
        $metadata->set('key', 'value');
        $metadata->remove('key');

        $this->assertFalse($metadata->has('key'));
        $this->assertNull($metadata->get('key'));
    }

    public function testToArray(): void
    {
        $metadata = new PayloadMetadata(['a' => 1, 'b' => 2]);

        $this->assertEquals(['a' => 1, 'b' => 2], $metadata->toArray());
    }

    public function testJsonSerialize(): void
    {
        $metadata = new PayloadMetadata(['x' => 'y']);
        $json = json_encode($metadata);

        $this->assertEquals('{"x":"y"}', $json);
    }

    public function testFromArrayWithChainedJobs(): void
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

    public function testFromArrayWithoutChainedJobs(): void
    {
        $data = ['key' => 'value'];
        $metadata = PayloadMetadata::fromArray($data);

        $this->assertEquals('value', $metadata->get('key'));
        $this->assertFalse($metadata->hasChainedJobs());
    }
}
