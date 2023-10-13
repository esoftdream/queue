<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Exceptions;

class QueueException extends \RuntimeException
{
    public static function forIncorrectHandler(): self
    {
        return new self('Incorrect handler name.');
    }

    public static function forIncorrectJobHandler(): self
    {
        return new self('Incorrect job handler name.');
    }

    public static function forIncorrectQueue(): self
    {
        return new self('Incorrect queue name.');
    }

    public static function forIncorrectPriority(): self
    {
        return new self('Incorrect priority value.');
    }

    public static function forHandlerNotAvailable(string $handler): self
    {
        return new self("Handler '{$handler}' is not available.");
    }

    public static function forJobFailed(string $message): self
    {
        return new self("Job failed: {$message}");
    }
}
