<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'unlock_condition',
        'required_success_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'required_success_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at');
    }
}
