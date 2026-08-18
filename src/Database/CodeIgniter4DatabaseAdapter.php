<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Database;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Ttpryg\Queue\Contracts\DatabaseAdapterInterface;
use Ttpryg\Queue\Enums\Status;

class CodeIgniter4DatabaseAdapter implements DatabaseAdapterInterface
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function insert(string $table, array $data): string|int
    {
        $this->db->table($table)->insert($data);

        return $this->db->insertID();
    }

    public function fetchWaitingJob(string $table, string $queue, array $priorities = []): ?object
    {
        $now = date('Y-m-d H:i:s');

        $builder = $this->db->table($table)
            ->where('queue', $queue)
            ->where('status', Status::Waiting->value)
            ->where('available_at <=', $now);

        if (! empty($priorities)) {
            $builder->whereIn('priority', $priorities);
        }

        $builder->orderBy('priority', 'DESC');
        $builder->orderBy('available_at', 'ASC');
        $builder->limit(1);

        $row = $builder->get()->getRow();

        return $row ?: null;
    }

    public function update(string $table, array $data, array $where): bool
    {
        $builder = $this->db->table($table);

        foreach ($where as $col => $val) {
            $builder->where($col, $val);
        }

        return (bool) $builder->update($data);
    }

    public function delete(string $table, array $where): int
    {
        $builder = $this->db->table($table);

        foreach ($where as $col => $val) {
            if (is_array($val)) {
                $builder->whereIn($col, $val);
            } else {
                $builder->where($col, $val);
            }
        }

        $builder->delete();

        return $this->db->affectedRows();
    }

    public function fetchFailed(string $table, ?string $queue = null, ?int $id = null): array
    {
        $builder = $this->db->table($table)->where('status', Status::Failed->value);

        if ($queue !== null) {
            $builder->where('queue', $queue);
        }

        if ($id !== null) {
            $builder->where('id', $id);
        }

        $builder->orderBy('updated_at', 'DESC');

        return $builder->get()->getResult();
    }
}
