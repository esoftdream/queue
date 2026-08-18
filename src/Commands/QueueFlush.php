<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QueueFlush extends BaseCommand
{
    protected $group = 'Queue';

    protected $name = 'queue:flush';

    protected $description = 'Flush jobs from failed queues.';

    protected $usage = 'queue:flush [options]';

    protected $options = [
        '-hours' => 'Number of hours.',
        '-queue' => 'Queue name.',
    ];

    public function run(array $params)
    {
        $hours = $params['hours'] ?? CLI::getOption('hours');
        $queue = $params['queue'] ?? CLI::getOption('queue');

        if ($hours !== null) {
            $hours = (int) $hours;
        }

        service('queue')->flush($hours, $queue);

        if ($hours === null) {
            CLI::write(sprintf('All failed jobs has been removed from the queue %s', $queue ?? 'default'), 'green');
        } else {
            CLI::write(sprintf('All failed jobs older than %s hours has been removed from the queue %s', $hours, $queue ?? 'default'), 'green');
        }

        return EXIT_SUCCESS;
    }
}
