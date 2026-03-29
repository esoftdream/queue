<?php

namespace Esoftdream\Queue\Tests\Support\Config;

class Queue
{
    public string $handler = 'sync';
    public string $transport = 'sync';
    public array $handlers = [];
    public int $maxJobAttempts = 3;
    public int $defaultRetryAfter = 60;
    public int $sleepOnFail = 3;
    public bool $failedJobLogging = true;
    public string $defaultQueue = 'default';
    public array $queues = ['default'];
    public array $queuePrefix = [];
    public array $jobMiddleware = [];
    public array $symfonyMessengerConfig = [];
}
