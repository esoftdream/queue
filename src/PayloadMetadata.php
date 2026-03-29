<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

use Esoftdream\Queue\Payloads\PayloadCollection;
use JsonSerializable;

class PayloadMetadata implements JsonSerializable
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function setChainedJobs(?PayloadCollection $payloads): self
    {
        if ($payloads !== null) {
            $this->data['chainedJobs'] = $payloads;
        } else {
            unset($this->data['chainedJobs']);
        }

        return $this;
    }

    public function getChainedJobs(): ?PayloadCollection
    {
        return $this->data['chainedJobs'] ?? null;
    }

    public function hasChainedJobs(): bool
    {
        return isset($this->data['chainedJobs']) && $this->data['chainedJobs']->count() > 0;
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
        return isset($this->data[$key]);
    }

    public function remove(string $key): self
    {
        unset($this->data[$key]);

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public static function fromArray(array $data): self
    {
        $metadata = new self();

        foreach ($data as $key => $value) {
            if ($key === 'chainedJobs' && is_array($value)) {
                $payloadCollection = new PayloadCollection();

                foreach ($value as $jobData) {
                    if (isset($jobData['job'], $jobData['data'])) {
                        $payload = \Esoftdream\Queue\Payloads\Payload::fromArray($jobData);
                        $payloadCollection->add($payload);
                    }
                }

                $metadata->setChainedJobs($payloadCollection);
            } else {
                $metadata->set($key, $value);
            }
        }

        return $metadata;
    }
}
