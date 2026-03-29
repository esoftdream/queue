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

class Registrar
{
    public static function Generators(): array
    {
        return [
            'views' => [
                'queue:job' => 'Esoftdream\Queue\Commands\Generators\Views\job.tpl.php',
            ],
        ];
    }
}
