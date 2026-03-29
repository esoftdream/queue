<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Esoftdream\Queue\Config\Queue as QueueConfig;

class QueueFailed extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:failed';
    protected $description = 'Display failed queue jobs.';
    protected $usage = 'queue:failed [options]';
    protected $options = [
        '-queue' => 'Queue name.',
    ];

    public function run(array $params)
    {
        $queue = $params['queue'] ?? CLI::getOption('queue');
        $config = config('Queue');
        $results = service('queue')->listFailed($queue);

        $thead = ['ID', 'Connection', 'Queue', 'Class', 'Failed At'];
        $tbody = [];

        foreach ($results as $result) {
            $tbody[] = [
                $result->id,
                $result->connection,
                $result->queue,
                $this->getClassName($result->payload['job'], $config),
                $result->failed_at,
            ];
        }

        CLI::table($tbody, $thead);

        return EXIT_SUCCESS;
    }

    private function getClassName(string $job, QueueConfig $config): string
    {
        return $config->jobHandlers[$job] ?? '';
    }
}
