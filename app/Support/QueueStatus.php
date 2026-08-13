<?php

namespace App\Support;

final class QueueStatus
{
    public static function variant(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'SEEN' => 'success',
            'IN_CONSULT', 'IN-CONSULT', 'IN CONSULT' => 'warning',
            'COMPLETED' => 'info',
            'WAITING', 'QUEUED', 'PENDING', 'TRIAGED', 'NEW' => 'info',
            default => 'info',
        };
    }

    public static function appointmentVariant(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'COMPLETED' => 'success',
            'CANCELLED', 'NO-SHOW', 'NO_SHOW' => 'danger',
            'PENDING', 'BOOKED', 'WAITING', 'QUEUED', 'CHECKED-IN', 'CHECKED_IN' => 'warning',
            'SEEN' => 'success',
            'IN_CONSULT', 'IN-CONSULT', 'IN CONSULT' => 'warning',
            default => 'info',
        };
    }

    public static function rowClass(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'SEEN' => 'bg-emerald-50/70',
            'IN_CONSULT', 'IN-CONSULT', 'IN CONSULT' => 'bg-amber-50/70',
            'COMPLETED' => 'bg-slate-100',
            default => '',
        };
    }

    public static function label(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'SEEN' => 'Seen',
            'IN_CONSULT', 'IN-CONSULT', 'IN CONSULT' => 'In consult',
            'COMPLETED' => 'Completed',
            'WAITING', 'QUEUED', 'PENDING', 'TRIAGED', 'NEW' => 'Waiting',
            default => 'Waiting',
        };
    }
}
