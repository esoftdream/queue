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
            'queue' => [static function ($getShared = true) {
                return Services::queue($getShared);
            }],
            'queueConfig' => [static function ($getShared = true) {
                return Services::queueConfig($getShared);
            }],
            'queueMessenger' => [static function ($getShared = true) {
                return Services::messenger($getShared);
            }],
            'queueDatabase' => [static function ($getShared = true) {
                return Services::databaseHandler($getShared);
            }],
        ];
    }
}
