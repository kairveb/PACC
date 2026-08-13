<?php

namespace App\Support;

final class PriorityColor
{
    public static function variant(?int $score): string
    {
        return match ((int) ($score ?? 5)) {
            1 => 'danger',
            2 => 'warning',
            3 => 'info',
            4 => 'success',
            default => 'success',
        };
    }

    public static function label(?int $score): string
    {
        return match ((int) ($score ?? 5)) {
            1 => 'Emergency',
            2 => 'Urgent',
            3 => 'Prompt',
            4 => 'Non-Urgent',
            default => 'Routine',
        };
    }
}
