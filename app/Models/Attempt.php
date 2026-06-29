<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'learning_session_id',
        'answer',
        'score',
        'success',
        'time_spent',
        'file_url',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'success' => 'boolean',
            'time_spent' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function learningSession(): BelongsTo
    {
        return $this->belongsTo(LearningSession::class);
    }
}
