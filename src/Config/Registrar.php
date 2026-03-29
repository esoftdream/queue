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
                return \Esoftdream\Queue\Config\Services::queue($getShared);
            }],
        ];
    }
}
