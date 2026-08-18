<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

use Esoftdream\Queue\Config\Queue as QueueConfig;
use Esoftdream\Queue\Database\CodeIgniter4DatabaseAdapter;
use Esoftdream\Queue\Events\CodeIgniter4EventDispatcher;
use Ttpryg\Queue\Contracts\QueueInterface;
use Ttpryg\Queue\QueueManager;

class Queue
{
    protected QueueManager $manager;

    public function __construct(protected QueueConfig $config)
    {
        $db = new CodeIgniter4DatabaseAdapter;
        $events = new CodeIgniter4EventDispatcher;

        /** @var \Psr\Log\LoggerInterface|null $logger */
        $logger = null;
        if (class_exists('Config\\Services')) {
            try {
                $logger = \Config\Services::logger();
            } catch (\Throwable) {
                $logger = null;
            }
        }

        $this->manager = new QueueManager($config->toQueueConfig(), $db, $logger, $events);
    }

    public function init(): QueueInterface
    {
        return $this->manager->init();
    }

    public function handler(string $name): QueueInterface
    {
        return $this->manager->handler($name);
    }
}
