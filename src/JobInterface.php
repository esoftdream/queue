<?php

declare(strict_types=1);

namespace Esoftdream\Queue;

interface JobInterface
{
    public function process(): void;

    public function getRetryAfter(): int;

    public function getTries(): int;

    public function getData(): array;
}
