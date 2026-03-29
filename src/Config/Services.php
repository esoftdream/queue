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

use CodeIgniter\Config\BaseService;
use Esoftdream\Queue\Config\Queue as QueueConfig;
use Esoftdream\Queue\Interfaces\QueueInterface;
use Esoftdream\Queue\Queue;

class Services extends BaseService
{
    public static function queue($getShared = true): QueueInterface
    {
        if ($getShared) {
            return static::getSharedInstance('queue');
        }

        /** @var QueueConfig $config */
        $config = config('Queue');

        return (new Queue($config))->init();
    }
}
