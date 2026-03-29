<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Config;

use CodeIgniter\Config\BaseConfig;
use Esoftdream\Queue\Interfaces\JobInterface;

class Queue extends BaseConfig
{
    public string $defaultHandler = 'database';

    public array $handlers = [
        'database' => \Esoftdream\Queue\Handlers\DatabaseHandler::class,
        'symfony' => \Esoftdream\Queue\Handlers\SymfonyMessengerHandler::class,
    ];

    public array $sync = [];

    public array $database = [
        'table' => 'queue_jobs',
    ];

    public array $symfonyMessenger = [
        'dsn' => 'sync://',
        'auto_setup' => true,
        'transports' => [
            'default' => 'sync://',
            'failed' => 'sync://',
        ],
        'failure_transport' => 'failed',
    ];

    public array $jobHandlers = [];

    public array $queueDefaultPriority = [];

    public array $queuePriorities = [];

    public bool $keepFailedJobs = true;

    public int $maxRetries = 3;

    public int $defaultRetryAfter = 60;

    public int $sleepOnFail = 3;

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->symfonyMessenger['dsn'] = 'sync://';
        }
    }

    public function resolveJobClass(string $name): string
    {
        if (!isset($this->jobHandlers[$name])) {
            throw new \RuntimeException("Job handler '{$name}' not found.");
        }

        return $this->jobHandlers[$name];
    }

    public function getQueuePriorities(string $name): ?string
    {
        if (!isset($this->queuePriorities[$name])) {
            return null;
        }

        return implode(',', $this->queuePriorities[$name]);
    }
}
