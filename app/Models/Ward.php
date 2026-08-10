<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    protected $guarded = [];

    protected $casts = ['active' => 'boolean'];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
