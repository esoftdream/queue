<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QueueForget extends BaseCommand
{
    protected $group = 'Queue';

    protected $name = 'queue:forget';

    protected $description = 'Remove ID from failed job queue.';

    protected $usage = 'queue:forget <id>';

    protected $arguments = [
        'id' => 'ID of the failed job.',
    ];

    public function run(array $params)
    {
        $id = array_shift($params);
        if ($id === null) {
            CLI::error('The ID of the failed job is not specified.');

            return EXIT_ERROR;
        }

        if (service('queue')->forget((int) $id)) {
            CLI::write(sprintf('Failed job with ID %s has been removed.', $id), 'green');
        } else {
            CLI::write(sprintf('Could not find the failed job with ID %s', $id), 'red');
        }

        return EXIT_SUCCESS;
    }
}
