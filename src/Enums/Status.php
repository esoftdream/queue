<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Enums;

enum Status: string
{
    case Waiting = 'waiting';
    case Reserved = 'reserved';
    case Done = 'done';
    case Failed = 'failed';
}
