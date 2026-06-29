<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'topic_id',
        'school_level_id',
        'title',
        'statement',
        'exercise_type',
        'options',
        'expected_answer',
        'correction',
        'points_max',
        'difficulty',
        'chapter',
        'is_active',
        'is_new',
        'file_url',
    ];

    protected $hidden = [
        'expected_answer',
    ];

    protected function casts(): array
    {
        return [
            'points_max' => 'integer',
            'difficulty' => 'integer',
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function optionsAsArray(): array
    {
        if ($this->options === null || trim($this->options) === '') {
            return [];
        }

        return array_values(array_filter(explode('|', $this->options)));
    }

    public function toApiArray(bool $withCorrection = false): array
    {
        $data = [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'school_level_id' => $this->school_level_id,
            'topic_name' => $this->topic?->name,
            'school_level_name' => $this->schoolLevel?->name,
            'title' => $this->title,
            'statement' => $this->statement,
            'exercise_type' => $this->exercise_type,
            'options' => $this->optionsAsArray(),
            'points_max' => $this->points_max,
            'difficulty' => $this->difficulty,
            'chapter' => $this->chapter,
            'is_active' => $this->is_active,
            'is_new' => $this->is_new,
            'file_url' => $this->file_url,
        ];

        if ($withCorrection) {
            $data['expected_answer'] = $this->expected_answer;
            $data['correction'] = $this->correction;
        }

        return $data;
    }
}
