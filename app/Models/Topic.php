<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
