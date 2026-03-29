<?php

namespace Esoftdream\Queue\Tests;

use Esoftdream\Queue\Payloads\Payload;
use Esoftdream\Queue\Payloads\PayloadCollection;
use Esoftdream\Queue\Tests\Support\TestCase;

class PayloadCollectionTest extends TestCase
{
    public function testAddAndCount(): void
    {
        $collection = new PayloadCollection();

        $collection->add(new Payload('Job1'));
        $collection->add(new Payload('Job2'));

        $this->assertEquals(2, $collection->count());
    }

    public function testShift(): void
    {
        $collection = new PayloadCollection();
        $payload1 = new Payload('Job1');
        $payload2 = new Payload('Job2');

        $collection->add($payload1);
        $collection->add($payload2);

        $shifted = $collection->shift();

        $this->assertSame($payload1, $shifted);
        $this->assertEquals(1, $collection->count());
    }

    public function testShiftWhenEmpty(): void
    {
        $collection = new PayloadCollection();

        $this->assertNull($collection->shift());
    }

    public function testIsEmpty(): void
    {
        $collection = new PayloadCollection();

        $this->assertTrue($collection->isEmpty());

        $collection->add(new Payload('Job'));

        $this->assertFalse($collection->isEmpty());
    }

    public function testAll(): void
    {
        $collection = new PayloadCollection();
        $payload1 = new Payload('Job1');
        $payload2 = new Payload('Job2');

        $collection->add($payload1);
        $collection->add($payload2);

        $items = $collection->all();

        $this->assertCount(2, $items);
        $this->assertSame($payload1, $items[0]);
        $this->assertSame($payload2, $items[1]);
    }

    public function testFifoOrder(): void
    {
        $collection = new PayloadCollection();

        for ($i = 1; $i <= 3; $i++) {
            $collection->add(new Payload("Job{$i}"));
        }

        $this->assertEquals('Job1', $collection->shift()->getJob());
        $this->assertEquals('Job2', $collection->shift()->getJob());
        $this->assertEquals('Job3', $collection->shift()->getJob());
        $this->assertTrue($collection->isEmpty());
    }
}
