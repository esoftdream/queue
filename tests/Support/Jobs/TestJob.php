<?php

namespace Esoftdream\Queue\Tests\Support\Jobs;

use Esoftdream\Queue\BaseJob;

class TestJob extends BaseJob
{
    public bool $processed = false;

    public array $handledData = [];

    public function process(): void
    {
        $this->processed = true;
        $this->handledData = $this->data;
    }

    public static function dispatch(array $data = []): void
    {
        // Mock dispatch
    }
}
