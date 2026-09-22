<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Config;

class Registrar
{
    public static function Database(): array
    {
        return [
            'DBGroup' => [],
        ];
    }

    public static function Services(): array
    {
        return [
            'queue' => [static fn ($getShared = true) => Services::queue($getShared)],
            'queueConfig' => [static fn ($getShared = true) => Services::queueConfig($getShared)],
            'queueMessenger' => [static fn ($getShared = true) => Services::messenger($getShared)],
            'queueDatabase' => [static fn ($getShared = true) => Services::databaseHandler($getShared)],
        ];
    }
}
