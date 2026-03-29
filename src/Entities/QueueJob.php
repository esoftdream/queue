<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Entities;

use Esoftdream\Queue\Payloads\Payload;

class QueueJob
{
    public int $attempts = 0;

    public function __construct(
        public ?string $id = null,
        public ?string $connection = null,
        public ?string $queue = null,
        public ?Payload $payload = null,
        public ?\DateTimeInterface $failed_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $job = new self();
        $job->id = $data['id'] ?? null;
        $job->connection = $data['connection'] ?? null;
        $job->queue = $data['queue'] ?? null;
        $job->failed_at = isset($data['failed_at']) ? new \DateTimeImmutable($data['failed_at']) : null;

        if (isset($data['payload'])) {
            $job->payload = Payload::fromArray($data['payload']);
        }

        return $job;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function incrementAttempts(): void
    {
        $this->attempts++;
    }
}
