<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Config;

use Esoftdream\Queue\Queue;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\SymfonyMessageMigrationUtility;

class Services
{
    public static function queue($getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('queue', function () {
                return new Queue(self::queueConfig());
            });
        }

        return new Queue(self::queueConfig());
    }

    public static function queueConfig($getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('queueConfig', function () {
                return new Queue();
            });
        }

        return new Queue();
    }

    public static function messenger($getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('messenger', function () {
                $config = self::queueConfig();
                
                return new \Esoftdream\Queue\Handlers\SymfonyMessengerHandler(
                    $config,
                    self::createMessageBus($config)
                );
            });
        }

        return new \Esoftdream\Queue\Handlers\SymfonyMessengerHandler(
            self::queueConfig(),
            self::createMessageBus(self::queueConfig())
        );
    }

    private static function createMessageBus(Queue $config): MessageBusInterface
    {
        return new \Esoftdream\Queue\Handlers\SymfonyMessengerHandler($config);
    }

    private static function getSharedInstance(string $name, callable $callback): mixed
    {
        static $instances = [];

        if (!isset($instances[$name])) {
            $instances[$name] = $callback();
        }

        return $instances[$name];
    }
}
