<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

class QueuePushResult
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly ?string $jobId = null,
        public readonly ?string $error = null
    ) {
    }

    public static function success(string|int $jobId): self
    {
        return new self(true, (string) $jobId);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }

    public function isFailed(): bool
    {
        return !$this->isSuccess;
    }
}
