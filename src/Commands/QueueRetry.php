<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QueueRetry extends BaseCommand
{
    protected $group = 'Queue';

    protected $name = 'queue:retry';

    protected $description = 'Retry one job or all jobs from failed queues.';

    protected $usage = 'queue:retry <id> [options]';

    protected $arguments = [
        'id' => 'ID of the failed job or "all" for all failed jobs.',
    ];

    protected $options = [
        '-queue' => 'Queue name.',
    ];

    public function run(array $params)
    {
        $id = array_shift($params);
        if ($id === null) {
            CLI::error('The ID of the failed job is not specified.');

            return EXIT_ERROR;
        }

        $id = $id === 'all' ? null : (int) $id;
        $queue = $params['queue'] ?? CLI::getOption('queue');
        $count = service('queue')->retry($id, $queue);

        if ($count === 0) {
            CLI::write(sprintf('No failed jobs has been restored to the queue %s', $queue ?? 'default'), 'red');
        } else {
            CLI::write(sprintf('%s failed job(s) has been restored to the queue %s', $count, $queue ?? 'default'), 'green');
        }

        return EXIT_SUCCESS;
    }
}
