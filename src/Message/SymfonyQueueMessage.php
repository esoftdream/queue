<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Message;

use Ttpryg\Queue\Message\SymfonyQueueMessage as CoreSymfonyQueueMessage;

class SymfonyQueueMessage extends CoreSymfonyQueueMessage
{
    public readonly array $metadata;

    public function __construct(
        string $job,
        array $data = [],
        array $options = []
    ) {
        parent::__construct($job, $data, $options);
        $this->metadata = $options;
    }

    public function getAttempts(): int
    {
        return $this->options['attempts'] ?? 0;
    }
}
