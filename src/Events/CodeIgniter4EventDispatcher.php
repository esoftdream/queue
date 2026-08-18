<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Events;

use CodeIgniter\Events\Events;
use Ttpryg\Queue\Contracts\EventDispatcherInterface;

class CodeIgniter4EventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object|string $event, array $metadata = []): object
    {
        $eventName = is_string($event) ? $event : get_class($event);
        $payload = isset($metadata['event']) ? $metadata['event'] : $event;

        Events::trigger($eventName, $payload);

        return is_string($event) ? (object) ['event' => $event, 'metadata' => $metadata] : $event;
    }
}
