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

namespace Esoftdream\Queue;

use Esoftdream\Queue\Config\Queue as QueueConfig;
use Esoftdream\Queue\Exceptions\QueueException;
use Esoftdream\Queue\Interfaces\QueueInterface;

class Queue
{
    public function __construct(protected QueueConfig $config)
    {
        if (! isset($config->handlers[$config->defaultHandler])) {
            throw QueueException::forIncorrectHandler();
        }
    }

    public function init(): QueueInterface
    {
        return new $this->config->handlers[$this->config->defaultHandler]($this->config);
    }
}
