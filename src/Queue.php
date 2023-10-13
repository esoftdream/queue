<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

use Esoftdream\Queue\Config\Queue as QueueConfig;
use Esoftdream\Queue\Exceptions\QueueException;
use Esoftdream\Queue\Interfaces\QueueInterface;

class Queue
{
    public function __construct(protected QueueConfig $config)
    {
        if (!isset($config->handlers[$config->defaultHandler])) {
            throw QueueException::forIncorrectHandler();
        }
    }

    public function init(): QueueInterface
    {
        $handlerClass = $this->config->handlers[$this->config->defaultHandler];

        return new $handlerClass($this->config);
    }

    public function handler(string $name): QueueInterface
    {
        if (!isset($this->config->handlers[$name])) {
            throw QueueException::forIncorrectHandler();
        }

        $handlerClass = $this->config->handlers[$name];

        return new $handlerClass($this->config);
    }
}
