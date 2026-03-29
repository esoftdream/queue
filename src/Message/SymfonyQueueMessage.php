<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Message;

class SymfonyQueueMessage
{
    public function __construct(
        public readonly string $job,
        public readonly array $data,
        public readonly array $metadata = []
    ) {
    }

    public function getQueue(): string
    {
        return $this->metadata['queue'] ?? 'default';
    }

    public function getPriority(): int
    {
        return $this->metadata['priority'] ?? 0;
    }

    public function getAttempts(): int
    {
        return $this->metadata['attempts'] ?? 0;
    }
}
