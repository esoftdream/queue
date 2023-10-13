# Esoftdream Queue

Queues for the CodeIgniter 4 framework with support for Database and Symfony Messenger backends.

[![PHPUnit](https://github.com/esoftdream/queue/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esoftdream/queue/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/esoftdream/queue/actions/workflows/phpstan.yml/badge.svg)](https://github.com/esoftdream/queue/actions/workflows/phpstan.yml)
[![License](https://img.shields.io/badge/License-MIT-blue)](LICENSE)

![PHP](https://img.shields.io/badge/PHP-%5E8.1-blue)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-%5E4.6-blue)

> [!NOTE]
> A queue system is typically used to handle resource-intensive or time-consuming tasks (e.g., image processing, sending emails) that are to be run in the background. It can also be a way to postpone certain activities that are to be executed automatically later.

## Installation

Install via composer:

```bash
composer require esoftdream/queue
```

Migrate your database (if using the database handler):

```bash
php spark migrate --all
```

## Configuration

Publish the configuration file to your `app/Config` directory:

```bash
php spark queue:publish
```

This will create `app/Config/Queue.php`. You can then customize your queue settings, such as the default handler and job handlers.

### Create your first Job

Generate a new job class:

```bash
php spark queue:job SendEmail
```

This will create `app/Jobs/SendEmail.php`. Open it and implement the `process()` method:

```php
<?php

namespace App\Jobs;

use Esoftdream\Queue\BaseJob;

class SendEmail extends BaseJob
{
    public function process(): void
    {
        $recipient = $this->data['email'];
        $subject   = $this->data['subject'];
        
        // Your logic to send email
        $email = \Config\Services::email();
        $email->setTo($recipient);
        $email->setSubject($subject);
        $email->setMessage('Hello from Queue!');
        $email->send();
    }
}
```

Register the job handler in `app/Config/Queue.php`:

```php
// ...
use App\Jobs\SendEmail;

public array $jobHandlers = [
    'send-email' => SendEmail::class
];
// ...
```

## Basic Usage

### Push a Job to the Queue

You can use the `service('queue')` to push a new job from your Controller or Model:

```php
service('queue')->push(
    'default',      // Queue name
    'send-email',   // Job handler alias
    ['email' => 'user@example.com', 'subject' => 'Welcome!'] // Job data
);
```

### Run the Queue Worker

To start processing jobs, run the worker command:

```bash
php spark queue:work default
```

## Advanced Usage

### Priority

You can set the priority for a job before pushing it to the queue. Higher numbers are processed first.

```php
service('queue')
    ->setPriority(10)
    ->push('default', 'send-email', ['email' => 'urgent@example.com']);
```

You can also define queue priorities in your `app/Config/Queue.php`:

```php
public array $queuePriorities = [
    'default' => [10, 5, 1],
];
```

And run the worker with specific priorities:

```bash
php spark queue:work default -priority 10,5
```

### Delay

Postpone job execution by a specific number of seconds:

```php
service('queue')
    ->setDelay(60) // Delay for 60 seconds
    ->push('default', 'send-email', ['email' => 'later@example.com']);
```

## Available Commands

- `queue:publish` - Publish configuration file.
- `queue:job` - Generate a new job class.
- `queue:work` - Start the worker.
- `queue:failed` - List failed jobs.
- `queue:retry` - Retry a failed job.
- `queue:forget` - Remove a failed job.
- `queue:flush` - Flush all failed jobs.
- `queue:clear` - Clear all jobs from a queue.
- `queue:stop` - Signal the worker to stop.

## Contributing

We accept and encourage contributions from the community. See the [CONTRIBUTING.md](CONTRIBUTING.md) file for details.

## License

This project is licensed under the MIT License.
