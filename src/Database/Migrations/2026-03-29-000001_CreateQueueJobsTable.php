<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueJobsTable extends Migration
{
    public function up(): void
    {
        // Ambil nama tabel secara dinamis dari config Queue
        $table = config('Queue')->database['table'] ?? 'queue_jobs';

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'queue' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'default',
            ],
            'job' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'payload' => [
                'type' => 'LONGTEXT',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'waiting',
            ],
            'priority' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'attempts' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
            ],
            'available_at' => [
                'type' => 'DATETIME',
            ],
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reserved_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'finished_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'exception' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'trace' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['queue', 'status', 'available_at', 'priority'], false, false, 'idx_queue_status_available');
        $this->forge->addKey('reserved_by');
        $this->forge->createTable($table, true);
    }

    public function down(): void
    {
        $table = config('Queue')->database['table'] ?? 'queue_jobs';
        $this->forge->dropTable($table, true);
    }
}
