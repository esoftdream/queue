<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Events;

use Psr\Http\Message\UriInterface;

class QueueEvent
{
    public function __construct(
        public readonly string $type,
        public readonly string $handler,
        public readonly ?string $queue = null,
        public readonly array $metadata = [],
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    public function getJobClass(): ?string
    {
        return $this->get('job_class');
    }

    public function getProcessingTime(): float
    {
        return $this->get('processing_time', 0.0);
    }

    public function getException(): ?\Throwable
    {
        return $this->get('exception');
    }

    public function getPriorities(): array
    {
        return $this->get('priorities', []);
    }

    public function getWorkerId(): ?string
    {
        return $this->get('worker_id');
    }

    public function getConfig(): array
    {
        return $this->get('config', []);
    }
}
