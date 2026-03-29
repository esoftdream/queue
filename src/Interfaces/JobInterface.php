<?php

declare(strict_types=1);

namespace Esoftdream\Queue\Interfaces;

interface JobInterface
{
    public function process(): void;

    public function getRetryAfter(): int;

    public function getTries(): int;

    public function getData(): array;

    public function setData(array $data): void;

    public function execute(): void;
}
