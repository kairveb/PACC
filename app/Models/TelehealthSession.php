<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelehealthSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public const STATUS_NOT_CONFIGURED = 'NOT_CONFIGURED';
    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_ONGOING = 'ONGOING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TelehealthParticipant::class);
    }

    public function isLive(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_ONGOING], true);
    }

    public function displayStatus(): string
    {
        return match ($this->status) {
            self::STATUS_ONGOING, self::STATUS_ACTIVE => 'Ongoing',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Not configured',
        };
    }

    public function secureJoinToken(): string
    {
        $payload = implode('|', [
            (string) $this->id,
            (string) ($this->appointment_id ?? 0),
            (string) ($this->start_time?->timestamp ?? 0),
            (string) ($this->updated_at?->timestamp ?? 0),
            (string) ($this->join_url ?? ''),
        ]);

        return hash_hmac('sha256', $payload, config('app.key'));
    }

    public function secureJoinUrl(): string
    {
        if (filled($this->join_url)) {
            $url = $this->join_url;
            if (str_contains($url, '?')) {
                return $url . '&token=' . rawurlencode($this->secureJoinToken());
            }

            return $url . '?token=' . rawurlencode($this->secureJoinToken());
        }

        return url('/telehealth/' . $this->id . '/join?token=' . rawurlencode($this->secureJoinToken()));
    }
}
