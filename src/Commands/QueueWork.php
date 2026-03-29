<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Esoftdream\Queue\Config\Queue as QueueConfig;
use Esoftdream\Queue\Entities\QueueJob;
use Esoftdream\Queue\Events\QueueEventManager;
use Esoftdream\Queue\Payloads\PayloadMetadata;
use Throwable;

class QueueWork extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:work';
    protected $description = 'Process jobs from a given queue.';
    protected $usage = 'queue:work <queueName> [options]';
    protected $arguments = [
        'queueName' => 'Name of the queue we will work with.',
    ];
    protected $options = [
        '-sleep'            => 'Wait time between the next check for available job when the queue is empty. Default value: 10 (seconds).',
        '-rest'             => 'Rest time between the jobs in the queue. Default value: 0 (seconds)',
        '-max-jobs'         => 'The maximum number of jobs to handle before worker should exit. Disabled by default.',
        '-max-time'         => 'The maximum number of seconds worker should run. Disabled by default.',
        '-memory'           => 'The maximum memory in MB that worker can take. Default value: 128',
        '-priority'         => 'The priority for the jobs from the queue (comma separated). If not provided explicit, will follow the priorities defined in the config via $queuePriorities for the given queue. Disabled by default.',
        '-tries'            => 'The number of attempts after which the job will be considered as failed. Overrides settings from the Job class. Disabled by default.',
        '-retry-after'      => 'The number of seconds after which the job is to be restarted in case of failure. Overrides settings from the Job class. Disabled by default.',
        '--stop-when-empty' => 'Stop when the queue is empty.',
    ];

    private string $workerId;
    private bool $running = true;

    public function run(array $params)
    {
        set_time_limit(0);

        $config = config('Queue');
        $stopWhenEmpty = false;
        $waiting = false;

        $queue = array_shift($params);
        if ($queue === null) {
            CLI::error('The queueName is not specified.');

            return EXIT_ERROR;
        }

        [
            $error,
            $sleep,
            $rest,
            $maxJobs,
            $maxTime,
            $memory,
            $priority,
            $tries,
            $retryAfter,
        ] = $this->readOptions($params, $config, $queue);

        if ($error !== null) {
            CLI::write($error, 'red');

            return EXIT_ERROR;
        }

        $countJobs = 0;

        if (array_key_exists('stop-when-empty', $params) || CLI::getOption('stop-when-empty')) {
            $stopWhenEmpty = true;
        }

        $startTime = microtime(true);
        $this->workerId = sprintf('worker-%s-%d', gethostname(), getmypid());

        CLI::write('Listening for the jobs with the queue: ' . CLI::color($queue, 'light_cyan'), 'cyan');

        if ($priority !== 'default') {
            CLI::write('Jobs will be consumed according to priority: ' . CLI::color($priority, 'light_cyan'), 'cyan');
        }

        CLI::write(PHP_EOL);

        $priority = array_map(trim(...), explode(',', (string) $priority));

        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }

        QueueEventManager::workerStarted(
            handler: service('queue')->name(),
            queue: $queue,
            priorities: $priority,
            config: [
                'max_jobs'     => $maxJobs,
                'max_time'     => $maxTime,
                'memory_limit' => $memory . 'MB',
                'sleep'        => $sleep,
                'rest'         => $rest,
            ],
            metadata: [
                'worker_id' => $this->workerId,
            ],
        );

        while ($this->running) {
            $work = service('queue')->pop($queue, $priority);

            if ($work === null) {
                if ($stopWhenEmpty) {
                    CLI::write('No job available. Stopping.', 'yellow');

                    $this->emitWorkerStoppedEvent($queue, $priority, $startTime, $countJobs, 'empty_queue');

                    return EXIT_SUCCESS;
                }

                if ($waiting === false) {
                    CLI::write('No job in the queue. Waiting...' . PHP_EOL, 'yellow');
                    $waiting = true;
                }

                sleep((int) $sleep);

                if ($this->checkMemory($memory)) {
                    $this->emitWorkerStoppedEvent($queue, $priority, $startTime, $countJobs, 'memory_limit');

                    return EXIT_SUCCESS;
                }

                if ($this->maxTimeCheck($maxTime, $startTime)) {
                    $this->emitWorkerStoppedEvent($queue, $priority, $startTime, $countJobs, 'time_limit');

                    return EXIT_SUCCESS;
                }
            } else {
                $waiting = false;
                $countJobs++;

                CLI::print('Starting a new job: ', 'cyan');
                CLI::print($work->payload['job'], 'light_cyan');
                CLI::print(', with ID: ', 'cyan');
                CLI::print((string) $work->id, 'light_cyan');

                $this->handleWork($work, $config, $tries, $retryAfter);

                if ($this->checkMemory($memory)) {
                    $this->emitWorkerStoppedEvent($queue, $priority, $startTime, $countJobs, 'memory_limit');

                    return EXIT_SUCCESS;
                }

                if ($this->maxJobsCheck($maxJobs, $countJobs)) {
                    $this->emitWorkerStoppedEvent($queue, $priority, $startTime, $countJobs, 'job_limit');

                    return EXIT_SUCCESS;
                }

                if ($this->maxTimeCheck($maxTime, $startTime)) {
                    $this->emitWorkerStoppedEvent($queue, $priority, $startTime, $countJobs, 'time_limit');

                    return EXIT_SUCCESS;
                }

                if ($rest > 0) {
                    sleep((int) $rest);
                }
            }
        }

        return EXIT_SUCCESS;
    }

    public function handleSignal(int $signal): void
    {
        $this->running = false;
        CLI::write(sprintf('Received signal %d. Worker will stop after current job.', $signal), 'yellow');
    }

    private function readOptions(array $params, QueueConfig $config, string $queue): array
    {
        $options = [
            'error'      => null,
            'sleep'      => $params['sleep'] ?? CLI::getOption('sleep') ?? 10,
            'rest'       => $params['rest'] ?? CLI::getOption('rest') ?? 0,
            'maxJobs'    => $params['max-jobs'] ?? CLI::getOption('max-jobs') ?? 0,
            'maxTime'    => $params['max-time'] ?? CLI::getOption('max-time') ?? 0,
            'memory'     => $params['memory'] ?? CLI::getOption('memory') ?? 128,
            'priority'   => $params['priority'] ?? CLI::getOption('priority') ?? $config->getQueuePriorities($queue) ?? 'default',
            'tries'      => $params['tries'] ?? CLI::getOption('tries'),
            'retryAfter' => $params['retry-after'] ?? CLI::getOption('retry-after'),
        ];

        $keys = ['sleep', 'rest', 'maxJobs', 'maxTime', 'memory', 'priority', 'tries', 'retryAfter'];

        foreach ($keys as $key) {
            if ($options[$key] === true) {
                $options['error'] = sprintf('Option: "-%s" must have a defined value.', $key);

                return array_values($options);
            }
        }

        $keys = array_diff($keys, ['priority']);

        foreach ($keys as $key) {
            if ($options[$key] !== null && ! is_int($options[$key])) {
                $options[$key] = (int) $options[$key];
            }
        }

        return array_values($options);
    }

    private function handleWork(QueueJob $work, QueueConfig $config, ?int $tries, ?int $retryAfter): void
    {
        timer()->start('work');
        $startTime = microtime(true);
        $payload   = $work->payload;

        $payloadMetadata = null;

        QueueEventManager::jobProcessingStarted(
            handler: service('queue')->name(),
            queue: $work->queue,
            job: $work,
            metadata: [
                'worker_id' => $this->workerId,
            ],
        );

        try {
            $payloadMetadata = PayloadMetadata::fromArray($payload['metadata'] ?? []);

            $this->renewLock($payloadMetadata);

            $class = $config->resolveJobClass($payload['job']);
            $job   = new $class($payload['data']);
            $job->process();

            service('queue')->done($work);

            QueueEventManager::jobProcessingCompleted(
                handler: service('queue')->name(),
                queue: $work->queue,
                job: $work,
                processingTime: microtime(true) - $startTime,
                metadata: [
                    'worker_id' => $this->workerId,
                ],
            );

            CLI::write('The processing of this job was successful', 'green');

            $this->processNextJobInChain($payloadMetadata);
        } catch (Throwable $err) {
            if (isset($job) && ++$work->attempts < ($tries ?? $job->getTries())) {
                service('queue')->later($work, $retryAfter ?? $job->getRetryAfter());
            } else {
                QueueEventManager::jobFailed(
                    handler: service('queue')->name(),
                    queue: $work->queue,
                    job: $work,
                    exception: $err,
                    processingTime: microtime(true) - $startTime,
                    metadata: [
                        'worker_id' => $this->workerId,
                    ],
                );

                service('queue')->failed($work, $err, $config->keepFailedJobs);
            }
            CLI::write('The processing of this job failed', 'red');
        } finally {
            $this->clearLock($payloadMetadata);

            timer()->stop('work');
            CLI::write(sprintf('It took: %s sec', timer()->getElapsedTime('work')) . PHP_EOL, 'cyan');
        }
    }

    private function processNextJobInChain(PayloadMetadata $payloadMetadata): void
    {
        if (! $payloadMetadata->hasChainedJobs()) {
            return;
        }

        $nextPayload = $payloadMetadata->getChainedJobs()->shift();
        $priority    = $nextPayload->getPriority();
        $delay       = $nextPayload->getDelay();

        if ($priority !== null) {
            service('queue')->setPriority($priority);
        }

        if ($delay !== null) {
            service('queue')->setDelay($delay);
        }

        if ($payloadMetadata->hasChainedJobs()) {
            $nextPayload->setChainedJobs($payloadMetadata->getChainedJobs());
        }

        service('queue')->push(
            $nextPayload->getQueue(),
            $nextPayload->getJob(),
            $nextPayload->getData(),
            $nextPayload->getMetadata(),
        );

        CLI::write(sprintf('Chained job: %s has been placed in the queue: %s', $nextPayload->getJob(), $nextPayload->getQueue()), 'green');
    }

    private function renewLock(PayloadMetadata $payloadMetadata): void
    {
        if (! $payloadMetadata->has('taskLockTTL') || ! $payloadMetadata->has('taskLockKey')) {
            return;
        }

        $ttl = $payloadMetadata->get('taskLockTTL');
        $key = $payloadMetadata->get('taskLockKey');

        if ($ttl === 0) {
            return;
        }

        cache()->save($key, [], $ttl);
    }

    private function clearLock(PayloadMetadata $payloadMetadata): void
    {
        if (! $payloadMetadata->has('taskLockKey')) {
            return;
        }

        $key = $payloadMetadata->get('taskLockKey');

        cache()->delete($key);
    }

    private function maxJobsCheck(int $maxJobs, int $countJobs): bool
    {
        if ($maxJobs > 0 && $countJobs >= $maxJobs) {
            CLI::write(sprintf('The maximum number of jobs (%s) has been reached. Stopping.', $maxJobs), 'yellow');

            return true;
        }

        return false;
    }

    private function maxTimeCheck(int $maxTime, float $startTime): bool
    {
        if ($maxTime > 0 && microtime(true) - $startTime >= $maxTime) {
            CLI::write(sprintf('The maximum time (%s sec) for worker to run has been reached. Stopping.', $maxTime), 'yellow');

            return true;
        }

        return false;
    }

    private function checkMemory(int $memory): bool
    {
        if (memory_get_usage(true) > $memory * 1024 * 1024) {
            CLI::write(sprintf('The memory limit of %s MB was reached. Stopping.', $memory), 'yellow');

            return true;
        }

        return false;
    }

    private function emitWorkerStoppedEvent(string $queue, array $priorities, float $startTime, int $jobsProcessed, string $reason): void
    {
        $uptime = microtime(true) - $startTime;

        QueueEventManager::workerStopped(
            handler: service('queue')->name(),
            queue: $queue,
            priorities: $priorities,
            uptime: $uptime,
            jobsProcessed: $jobsProcessed,
            metadata: [
                'worker_id'    => $this->workerId,
                'stop_reason'  => $reason,
                'memory_usage' => memory_get_usage(true),
                'memory_peak'  => memory_get_peak_usage(true),
            ],
        );
    }
}
