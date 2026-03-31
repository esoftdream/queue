<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Config;

use Esoftdream\Queue\Handlers\DatabaseHandler;
use Esoftdream\Queue\Handlers\SymfonyMessengerHandler;
use Esoftdream\Queue\Interfaces\QueueInterface;

class Services
{
    public static function queue(bool $getShared = true): QueueInterface
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queue');
        }

        return self::createHandler(self::queueConfig());
    }

    public static function queueConfig(bool $getShared = true): Queue
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueConfig');
        }

        /** @var Queue|null $config */
        $config = config('Queue');

        return $config ?? new Queue();
    }

    public static function messenger(bool $getShared = true): SymfonyMessengerHandler
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueMessenger');
        }

        return new SymfonyMessengerHandler(self::queueConfig(), self::logger());
    }

    public static function databaseHandler(bool $getShared = true): DatabaseHandler
    {
        if ($getShared) {
            return \Config\Services::getSharedInstance('queueDatabase');
        }

        return new DatabaseHandler(self::queueConfig(), self::logger());
    }

    private static function createHandler(Queue $config): QueueInterface
    {
        $handlerName = $config->defaultHandler;

        return match ($handlerName) {
            'database' => self::databaseHandler(false),
            'symfony'  => self::messenger(false),
            default    => new DatabaseHandler($config, self::logger()),
        };
    }

    private static function logger(): ?\Psr\Log\LoggerInterface
    {
        try {
            return \Config\Services::logger();
        } catch (\Throwable) {
            return null;
        }
    }
}
