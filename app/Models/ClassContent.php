<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassContent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'class_id',
        'title',
        'content',
        'content_type',
        'week_number',
        'order',
        'additional_notes',
        'attachments',
        'is_published',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachments' => 'array',
        'is_published' => 'boolean',
        'week_number' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Content types
     */
    public const TYPE_OUTLINE = 'outline';
    public const TYPE_LESSON = 'lesson';
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_RESOURCE = 'resource';
    public const TYPE_HOMEWORK = 'homework';
    public const TYPE_READING = 'reading';
    public const TYPE_VIDEO = 'video';
    public const TYPE_DOCUMENT = 'document';

    /**
     * Get the class this content belongs to
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(DiscipleshipClass::class, 'class_id');
    }

    /**
     * Get the user who created this content
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all content types
     */
    public static function getContentTypes(): array
    {
        return [
            self::TYPE_OUTLINE => 'Class Outline',
            self::TYPE_LESSON => 'Lesson',
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_RESOURCE => 'Resource',
            self::TYPE_HOMEWORK => 'Homework',
            self::TYPE_READING => 'Reading Material',
            self::TYPE_VIDEO => 'Video',
            self::TYPE_DOCUMENT => 'Document',
        ];
    }

    /**
     * Scope to get published content
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to get content by week
     */
    public function scopeByWeek($query, int $weekNumber)
    {
        return $query->where('week_number', $weekNumber);
    }

    /**
     * Scope to get content by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('content_type', $type);
    }
}
