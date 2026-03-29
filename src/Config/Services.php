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
        $config = self::queueConfig();

        if ($getShared) {
            return \Config\Services::getSharedInstance('queue', function () use ($config) {
                return self::createHandler($config);
            });
        }

        return self::createHandler($config);
    }

    public static function queueConfig(bool $getShared = true): Queue
    {
        return \Config\Services::getConfigInstance('queue', $getShared, function () {
            return new Queue();
        });
    }

    public static function messenger(bool $getShared = true): SymfonyMessengerHandler
    {
        $config = self::queueConfig();

        if ($getShared) {
            return \Config\Services::getSharedInstance('queueMessenger', function () use ($config) {
                return new SymfonyMessengerHandler($config, self::logger());
            });
        }

        return new SymfonyMessengerHandler($config, self::logger());
    }

    public static function databaseHandler(bool $getShared = true): DatabaseHandler
    {
        $config = self::queueConfig();

        if ($getShared) {
            return \Config\Services::getSharedInstance('queueDatabase', function () use ($config) {
                return new DatabaseHandler($config, self::logger());
            });
        }

        return new DatabaseHandler($config, self::logger());
    }

    private static function createHandler(Queue $config): QueueInterface
    {
        $handlerName = $config->defaultHandler;

        return match ($handlerName) {
            'database' => self::databaseHandler(false),
            'symfony' => self::messenger(false),
            default => new DatabaseHandler($config, self::logger()),
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
