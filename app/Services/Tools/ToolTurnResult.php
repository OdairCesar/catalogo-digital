<?php

namespace App\Services\Tools;

final readonly class ToolTurnResult
{
    public function __construct(
        public string $reply,
        public bool $readyForReport,
    ) {}
}
