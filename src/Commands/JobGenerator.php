<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class JobGenerator extends BaseCommand
{
    use GeneratorTrait;

    protected $group = 'Queue';

    protected $name = 'queue:job';

    protected $description = 'Generates a new job file.';

    protected $usage = 'queue:job <name> [options]';

    protected $arguments = [
        'name' => 'The job class name.',
    ];

    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix' => 'Append the component title to the class name (e.g. Email => EmailJob).',
        '--force' => 'Force overwrite existing file.',
    ];

    public function run(array $params)
    {
        $this->component = 'Job';
        $this->directory = 'Jobs';
        $this->template = 'job.tpl.php';

        $this->classNameLang = 'Queue.generator.className.job';

        $this->generateClass($params);

        return EXIT_SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderTemplate(array $data = []): string
    {
        $source = service('autoloader')->getNamespace('Esoftdream\\Queue')[0];
        $template = $source.'/Commands/Generators/Views/job.tpl.php';

        return file_get_contents($template);
    }
}
