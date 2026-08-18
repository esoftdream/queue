<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

use Ttpryg\Queue\Entities\QueuePushResult as CoreQueuePushResult;

class QueuePushResult extends CoreQueuePushResult
{
    public readonly ?string $error;

    public function __construct(
        bool $isSuccess,
        string|int|null $jobId = null,
        ?string $errorMessage = null
    ) {
        $jobIdStr = $jobId !== null ? (string) $jobId : null;
        parent::__construct($isSuccess, $jobIdStr, $errorMessage);
        $this->error = $errorMessage;
    }

    public static function success(string|int $jobId): self
    {
        return new self(true, (string) $jobId, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }

    public function isFailed(): bool
    {
        return !$this->isSuccess;
    }
}
