<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Config;

use CodeIgniter\Config\BaseConfig;
use Ttpryg\Queue\Config\QueueConfig;
use Ttpryg\Queue\Handlers\DatabaseHandler;
use Ttpryg\Queue\Handlers\SymfonyMessengerHandler;

class Queue extends BaseConfig
{
    public string $defaultHandler = 'database';

    public array $handlers = [
        'database' => DatabaseHandler::class,
        'symfony' => SymfonyMessengerHandler::class,
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

        $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
        if ($env === 'testing') {
            $this->symfonyMessenger['dsn'] = 'sync://';
        }
    }

    public function toQueueConfig(): QueueConfig
    {
        return new QueueConfig(
            defaultHandler: $this->defaultHandler,
            handlers: $this->handlers,
            sync: $this->sync,
            database: $this->database,
            symfonyMessenger: $this->symfonyMessenger,
            jobHandlers: $this->jobHandlers,
            queueDefaultPriority: $this->queueDefaultPriority,
            queuePriorities: $this->queuePriorities,
            keepFailedJobs: $this->keepFailedJobs,
            maxRetries: $this->maxRetries,
            defaultRetryAfter: $this->defaultRetryAfter,
            sleepOnFail: $this->sleepOnFail
        );
    }

    public function resolveJobClass(string $name): string
    {
        if (! isset($this->jobHandlers[$name])) {
            throw new \RuntimeException("Job handler '{$name}' not found.");
        }

        return $this->jobHandlers[$name];
    }

    public function getQueuePriorities(string $name): ?string
    {
        if (! isset($this->queuePriorities[$name])) {
            return null;
        }

        return implode(',', $this->queuePriorities[$name]);
    }
}
