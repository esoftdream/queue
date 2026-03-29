<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Payloads;

class Payload
{
    public function __construct(
        public readonly string $job,
        public readonly array $data = [],
        public readonly ?array $metadata = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['job'] ?? '',
            $data['data'] ?? [],
            $data['metadata'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'job' => $this->job,
            'data' => $this->data,
            'metadata' => $this->metadata,
        ];
    }

    public function getJob(): string
    {
        return $this->job;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }
}
