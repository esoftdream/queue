<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueJobsTable extends Migration
{
    protected $table = 'queue_jobs';

    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
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
                'type' => 'ENUM',
                'constraint' => ['waiting', 'reserved', 'done', 'failed'],
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
                'type' => 'TEXT',
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
        $this->forge->addKey(['queue', 'status']);
        $this->forge->addKey(['queue', 'available_at']);
        $this->forge->addKey('reserved_by');
        $this->forge->createTable($this->table);
    }

    public function down(): void
    {
        $this->forge->dropTable($this->table);
    }
}
