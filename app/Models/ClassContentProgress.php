<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassContentProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_content_id',
        'class_enrollment_id',
        'is_completed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Content associated with this progress entry.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(ClassContent::class, 'class_content_id');
    }

    /**
     * Enrollment associated with this progress entry.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ClassEnrollment::class, 'class_enrollment_id');
    }
}

