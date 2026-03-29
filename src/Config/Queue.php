<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Queue.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Esoftdream\Queue\Config;

use CodeIgniter\Config\BaseConfig;
use Esoftdream\Queue\Exceptions\QueueException;
use Esoftdream\Queue\Handlers\DatabaseHandler;
use Esoftdream\Queue\Handlers\PredisHandler;
use Esoftdream\Queue\Handlers\RabbitMQHandler;
use Esoftdream\Queue\Handlers\RedisHandler;
use Esoftdream\Queue\Interfaces\JobInterface;
use Esoftdream\Queue\Interfaces\QueueInterface;

class Queue extends BaseConfig
{
    /**
     * Default handler.
     */
    public string $defaultHandler = 'database';

    /**
     * Available handlers.
     *
     * @var array<string, class-string<QueueInterface>>
     */
    public array $handlers = [
        'database' => DatabaseHandler::class,
        'redis'    => RedisHandler::class,
        'predis'   => PredisHandler::class,
        'rabbitmq' => RabbitMQHandler::class,
    ];

    /**
     * Database handler config.
     */
    public array $database = [
        'dbGroup'   => 'default',
        'getShared' => true,
        // use skip locked feature to maintain concurrency calls
        // this is not relevant for the SQLite3 database driver
        'skipLocked' => true,
    ];

    /**
     * Redis handler config.
     */
    public array $redis = [
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
        'prefix'   => '',
    ];

    /**
     * Predis handler config.
     */
    public array $predis = [
        'scheme'   => 'tcp',
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 5,
        'database' => 0,
        'prefix'   => '',
    ];

    /**
     * RabbitMQ handler config.
     */
    public array $rabbitmq = [
        'host'     => '127.0.0.1',
        'port'     => 5672,
        'user'     => 'guest',
        'password' => 'guest',
        'vhost'    => '/',
    ];

    /**
     * Whether to save failed jobs for later review.
     */
    public bool $keepFailedJobs = true;

    /**
     * Default priorities for the queue
     * if different from the "default".
     */
    public array $queueDefaultPriority = [];

    /**
     * Valid priorities in the order for the queue,
     * if different from the "default".
     */
    public array $queuePriorities = [];

    /**
     * Your jobs handlers.
     *
     * @var array<string, class-string<JobInterface>>
     */
    public array $jobHandlers = [];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->database['dbGroup'] = config('database')->defaultGroup;
        }
    }

    /**
     * Resolve job class name.
     *
     * @return class-string<JobInterface>
     */
    public function resolveJobClass(string $name): string
    {
        if (! isset($this->jobHandlers[$name])) {
            throw QueueException::forIncorrectJobHandler();
        }

        return $this->jobHandlers[$name];
    }

    /**
     * Stringify queue priorities.
     */
    public function getQueuePriorities(string $name): ?string
    {
        if (! isset($this->queuePriorities[$name])) {
            return null;
        }

        return implode(',', $this->queuePriorities[$name]);
    }
}
