<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QueueStop extends BaseCommand
{
    protected $group = 'Queue';

    protected $name = 'queue:stop';

    protected $description = 'Stop a given queue.';

    protected $usage = 'queue:stop <queueName>';

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

        $startTime = microtime(true);
        $cacheName = sprintf('queue-%s-stop', $queue);

        cache()->save($cacheName, $startTime, MINUTE * 10);

        CLI::write('Queue will be stopped after the current job finish', 'yellow');

        return EXIT_SUCCESS;
    }
}
