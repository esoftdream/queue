<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

use Esoftdream\Queue\Payloads\PayloadCollection;
use JsonSerializable;
use Ttpryg\Queue\Entities\PayloadMetadata as CorePayloadMetadata;

class PayloadMetadata extends CorePayloadMetadata implements JsonSerializable
{
    protected array $data = [];
    protected ?PayloadCollection $chainedJobs = null;

    public function __construct(
        string|array|null $userIdOrData = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $requestId = null,
        ?string $createdAt = null,
        array $custom = []
    ) {
        if (is_array($userIdOrData)) {
            $this->data = $userIdOrData;
            parent::__construct(
                $userIdOrData['user_id'] ?? null,
                $userIdOrData['ip_address'] ?? null,
                $userIdOrData['user_agent'] ?? null,
                $userIdOrData['request_id'] ?? null,
                $userIdOrData['created_at'] ?? null,
                $userIdOrData['custom'] ?? []
            );

            if (isset($userIdOrData['chainedJobs']) && is_array($userIdOrData['chainedJobs'])) {
                $collection = new PayloadCollection();
                foreach ($userIdOrData['chainedJobs'] as $jobData) {
                    $collection->add(\Esoftdream\Queue\Payloads\Payload::fromArray($jobData));
                }
                $this->setChainedJobs($collection);
            }
        } else {
            parent::__construct($userIdOrData, $ipAddress, $userAgent, $requestId, $createdAt, $custom);
            $this->data = $this->toArray();
        }
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): self
    {
        unset($this->data[$key]);
        return $this;
    }

    public function setChainedJobs(?PayloadCollection $chainedJobs): self
    {
        $this->chainedJobs = $chainedJobs;
        return $this;
    }

    public function getChainedJobs(): ?PayloadCollection
    {
        return $this->chainedJobs;
    }

    public function hasChainedJobs(): bool
    {
        return $this->chainedJobs !== null && !$this->chainedJobs->isEmpty();
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
