<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentType extends Model
{
    protected $guarded = [];

    protected $casts = ['default_duration' => 'integer', 'telehealth' => 'boolean', 'active' => 'boolean'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
