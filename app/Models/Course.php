<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'topic_id',
        'school_level_id',
        'title',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function chapters(): HasMany
    {
        return $this->hasMany(CourseChapter::class)->orderBy('display_order');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'school_level_id' => $this->school_level_id,
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'chapters' => $this->chapters->map(fn (CourseChapter $chapter) => $chapter->toApiArray())->values(),
        ];
    }
}
