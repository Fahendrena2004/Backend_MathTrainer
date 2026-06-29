<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolLevel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'cycle',
        'display_order',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
