<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Enums;

enum Status: string
{
    case PENDING = 'pending';
    case RESERVED = 'reserved';
    case DONE = 'done';
    case FAILED = 'failed';
}
