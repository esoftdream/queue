<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Config;

use Esoftdream\Queue\Database\CodeIgniter4DatabaseAdapter;
use Esoftdream\Queue\Events\CodeIgniter4EventDispatcher;
use Psr\Log\LoggerInterface;
use Ttpryg\Queue\Contracts\QueueInterface;
use Ttpryg\Queue\QueueManager;

class Services
{
    public static function queue(bool $getShared = true): QueueInterface
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queue');
        }

        return self::queueManager(false)->init();
    }

    public static function queueManager(bool $getShared = true): QueueManager
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueManager');
        }

        $config = self::queueConfig()->toQueueConfig();
        $db = new CodeIgniter4DatabaseAdapter;
        $logger = self::logger();
        $events = new CodeIgniter4EventDispatcher;

        return new QueueManager($config, $db, $logger, $events);
    }

    public static function queueConfig(bool $getShared = true): Queue
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueConfig');
        }

        /** @var Queue|null $config */
        $config = config('Queue');

        return $config ?? new Queue;
    }

    public static function messenger(bool $getShared = true): QueueInterface
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueMessenger');
        }

        return self::queueManager(false)->handler('symfony');
    }

    public static function databaseHandler(bool $getShared = true): QueueInterface
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueDatabase');
        }

        return self::queueManager(false)->handler('database');
    }

    private static function logger(): ?LoggerInterface
    {
        try {
            return \Config\Services::logger();
        } catch (\Throwable) {
            return null;
        }
    }
}
