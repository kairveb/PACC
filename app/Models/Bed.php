<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    protected $guarded = [];

    protected $casts = ['status_updated_at' => 'datetime'];

    public const STATUS_AVAILABLE = 'AVAILABLE';
    public const STATUS_OCCUPIED = 'OCCUPIED';
    public const STATUS_RESERVED = 'RESERVED';
    public const STATUS_CLEANING = 'CLEANING';
    public const STATUS_MAINTENANCE = 'MAINTENANCE';
    public const STATUS_BLOCKED = 'BLOCKED';

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BedAssignment::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BedReservation::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(BedAssignment::class)
            ->where('status', 'ACTIVE')
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');
    }

    public function activeReservation(): HasOne
    {
        return $this->hasOne(BedReservation::class)->where('status', 'ACTIVE');
    }

    public function getLabelAttribute(): string
    {
        return $this->room?->ward?->code . ' / ' . $this->room?->number . ' / Bed ' . $this->number;
    }
}
