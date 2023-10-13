<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Handlers;

use CodeIgniter\Database\BaseConnection;
use Esoftdream\Queue\Config\Queue as QueueConfig;
use Esoftdream\Queue\Entities\QueueJob;
use Esoftdream\Queue\Enums\Status;
use Esoftdream\Queue\Interfaces\QueueInterface;
use Esoftdream\Queue\PayloadMetadata;
use Esoftdream\Queue\QueuePushResult;
use Psr\Log\LoggerInterface;

class DatabaseHandler implements QueueInterface
{
    protected ?int $delay = null;
    protected ?int $priority = null;
    protected BaseConnection $db;
    protected string $table = 'queue_jobs';

    public function __construct(
        protected QueueConfig $config,
        protected ?LoggerInterface $logger = null
    ) {
        $this->db = \Config\Database::connect();
        $this->table = $config->database['table'] ?? 'queue_jobs';
    }

    public function name(): string
    {
        return 'database';
    }

    public function push(string $queue, string $job, array $data, ?PayloadMetadata $metadata = null): QueuePushResult
    {
        $this->validateJobAndPriority($queue, $job);

        $insertData = [
            'queue' => $queue,
            'job' => $job,
            'payload' => json_encode([
                'job' => $job,
                'data' => $data,
                'metadata' => $metadata?->toArray(),
            ]),
            'status' => Status::Waiting->value,
            'priority' => $this->priority ?? 0,
            'attempts' => 0,
            'available_at' => $this->delay !== null
                ? date('Y-m-d H:i:s', time() + $this->delay)
                : date('Y-m-d H:i:s'),
            'reserved_at' => null,
            'reserved_by' => null,
            'finished_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $this->db->table($this->table)->insert($insertData);
            $insertId = $this->db->insertID();

            $this->delay = null;
            $this->priority = null;

            return QueuePushResult::success((string) $insertId);
        } catch (\Throwable $e) {
            $this->logger?->error('Failed to push job: ' . $e->getMessage());
            return QueuePushResult::failure($e->getMessage());
        }
    }

    public function pop(string $queue, array $priorities = []): ?QueueJob
    {
        $now = date('Y-m-d H:i:s');

        $builder = $this->db->table($this->table)
            ->where('queue', $queue)
            ->where('status', Status::Waiting->value)
            ->where('available_at <=', $now);

        if (!empty($priorities)) {
            $builder->whereIn('priority', $priorities);
        }

        $builder->orderBy('priority', 'DESC');
        $builder->orderBy('available_at', 'ASC');
        $builder->limit(1);

        $row = $builder->get()->getRow();

        if ($row === null) {
            return null;
        }

        $updateData = [
            'status' => Status::Reserved->value,
            'reserved_at' => $now,
            'reserved_by' => gethostname(),
            'attempts' => $row->attempts + 1,
            'updated_at' => $now,
        ];

        $this->db->table($this->table)->where('id', $row->id)->update($updateData);

        return QueueJob::fromObject($row);
    }

    public function later(QueueJob $queueJob, int $seconds): bool
    {
        $availableAt = date('Y-m-d H:i:s', time() + $seconds);

        return (bool) $this->db->table($this->table)
            ->where('id', $queueJob->id)
            ->update([
                'available_at' => $availableAt,
                'status' => Status::Waiting->value,
                'reserved_at' => null,
                'reserved_by' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function failed(QueueJob $queueJob, \Throwable $err, bool $keepJob = true): bool
    {
        $updateData = [
            'status' => Status::Failed->value,
            'finished_at' => date('Y-m-d H:i:s'),
            'exception' => $err->getMessage(),
            'trace' => $err->getTraceAsString(),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $result = (bool) $this->db->table($this->table)
            ->where('id', $queueJob->id)
            ->update($updateData);

        if (!$keepJob) {
            $this->db->table($this->table)->where('id', $queueJob->id)->delete();
        }

        return $result;
    }

    public function done(QueueJob $queueJob): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('id', $queueJob->id)
            ->update([
                'status' => Status::Done->value,
                'finished_at' => date('Y-m-d H:i:s'),
                'reserved_at' => null,
                'reserved_by' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function clear(?string $queue = null): bool
    {
        $builder = $this->db->table($this->table)->whereIn('status', [
            Status::Waiting->value,
            Status::Reserved->value,
        ]);

        if ($queue !== null) {
            $builder->where('queue', $queue);
        }

        return (bool) $builder->delete();
    }

    public function getQueues(): array
    {
        return array_keys($this->config->handlers);
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function delay(int $delay): self
    {
        $this->delay = $delay;
        return $this;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function setDelay(int $delay): self
    {
        $this->delay = $delay;
        return $this;
    }

    public function listFailed(?string $queue = null): array
    {
        $builder = $this->db->table($this->table)->where('status', Status::Failed->value);

        if ($queue !== null) {
            $builder->where('queue', $queue);
        }

        $builder->orderBy('updated_at', 'DESC');

        $results = [];
        foreach ($builder->get()->getResult() as $row) {
            $results[] = QueueJob::fromObject($row);
        }

        return $results;
    }

    public function retry(?int $id, ?string $queue = null): int
    {
        $builder = $this->db->table($this->table)->where('status', Status::Failed->value);

        if ($id !== null) {
            $builder->where('id', $id);
        }

        if ($queue !== null) {
            $builder->where('queue', $queue);
        }

        $jobs = $builder->get()->getResult();

        $count = 0;
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);

            $this->push(
                $job->queue,
                $job->job,
                $payload['data'] ?? [],
                isset($payload['metadata'])
                    ? PayloadMetadata::fromArray($payload['metadata'])
                    : null
            );

            $this->db->table($this->table)->where('id', $job->id)->delete();
            $count++;
        }

        return $count;
    }

    public function forget(int $id): bool
    {
        $affected = $this->db->table($this->table)
            ->where('id', $id)
            ->where('status', Status::Failed->value)
            ->delete();

        return $affected > 0;
    }

    public function flush(?int $hours = null, ?string $queue = null): void
    {
        $builder = $this->db->table($this->table)->where('status', Status::Failed->value);

        if ($queue !== null) {
            $builder->where('queue', $queue);
        }

        if ($hours !== null) {
            $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));
            $builder->where('updated_at <=', $cutoff);
        }

        $builder->delete();
    }

    private function validateJobAndPriority(string $queue, string $job): void
    {
        if ($this->priority !== null && $this->priority < 0) {
            throw new \InvalidArgumentException('Priority must be a positive integer.');
        }
    }
}
