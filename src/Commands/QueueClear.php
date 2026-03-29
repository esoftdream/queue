<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QueueClear extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:clear';
    protected $description = 'Clear all jobs from a given queue.';
    protected $usage = 'queue:clear <queueName>';
    protected $arguments = [
        'queueName' => 'Name of the queue we will work with.',
    ];

    public function run(array $params)
    {
        $queue = array_shift($params);
        if ($queue === null) {
            CLI::error('The queueName is not specified.');

            return EXIT_ERROR;
        }

        service('queue')->clear($queue);

        CLI::print('Queue ', 'yellow');
        CLI::print($queue, 'light_yellow');
        CLI::print(' has been cleared.', 'yellow');

        return EXIT_SUCCESS;
    }
}
