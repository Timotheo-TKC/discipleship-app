<?php

namespace App\Models;

use App\Events\ClassCompleted;
use App\Models\ClassContent;
use App\Models\ClassContentProgress;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'member_id',
        'status',
        'notes',
        'enrolled_at',
        'approved_at',
        'approved_by',
        'completed_lessons',
        'progress_percentage',
        'attendance_rate',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'approved_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
    ];

    /**
     * Boot the model and register events
     */
    protected static function boot()
    {
        parent::boot();

        // Fire ClassCompleted event when enrollment status changes to completed
        static::updated(function ($enrollment) {
            if ($enrollment->isDirty('status') && $enrollment->status === 'completed') {
                event(new ClassCompleted($enrollment));
            }
        });
    }

    /**
     * Get the class this enrollment is for
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(DiscipleshipClass::class, 'class_id');
    }

    /**
     * Progress records for class contents.
     */
    public function contentProgress(): HasMany
    {
        return $this->hasMany(ClassContentProgress::class);
    }

    /**
     * Get progress record for specific content.
     */
    public function progressForContent(ClassContent $content): ?ClassContentProgress
    {
        return $this->contentProgress
            ? $this->contentProgress->firstWhere('class_content_id', $content->id)
            : $this->contentProgress()
                ->where('class_content_id', $content->id)
                ->first();
    }

    /**
     * Determine if content has been completed.
     */
    public function hasCompletedContent(ClassContent $content): bool
    {
        $progress = $this->progressForContent($content);

        return $progress ? $progress->is_completed : false;
    }

    /**
     * Calculate progress states for ordered content list.
     *
     * @param \Illuminate\Support\Collection<int,\App\Models\ClassContent> $orderedContents
     * @return array<int, array{completed: bool, locked: bool, progress: ?\App\Models\ClassContentProgress}>
     */
    public function buildProgressStates(Collection $orderedContents): array
    {
        $this->loadMissing('contentProgress');

        $progressMap = $this->contentProgress->keyBy('class_content_id');
        $states = [];
        $allPreviousCompleted = true;

        foreach ($orderedContents as $content) {
            $progress = $progressMap->get($content->id);
            $completed = $progress ? $progress->is_completed : false;

            $locked = !$allPreviousCompleted && ! $completed;

            $states[$content->id] = [
                'completed' => $completed,
                'locked' => $locked,
                'progress' => $progress,
            ];

            if (! $completed && $allPreviousCompleted) {
                $allPreviousCompleted = false;
            }
        }

        return $states;
    }

    /**
     * Mark a content item as completed or not.
     */
    public function setContentCompletion(ClassContent $content, bool $completed): ClassContentProgress
    {
        $this->loadMissing('contentProgress');

        $progress = $this->contentProgress()
            ->firstOrCreate(
                [
                    'class_content_id' => $content->id,
                ],
                [
                    'started_at' => now(),
                ]
            );

        $progress->is_completed = $completed;
        $progress->completed_at = $completed ? now() : null;
        $progress->save();

        $this->recalculateProgressMetrics();

        return $progress;
    }

    /**
     * Refresh enrollment metrics after progress update.
     */
    public function recalculateProgressMetrics(): void
    {
        $this->loadMissing(['class', 'class.publishedContents']);

        $totalPublished = $this->class
            ? $this->class->publishedContents()
                ->count()
            : 0;

        $completed = $this->contentProgress()
            ->where('is_completed', true)
            ->count();

        $percentage = $totalPublished > 0
            ? round(($completed / $totalPublished) * 100, 2)
            : 0;

        $this->forceFill([
            'completed_lessons' => $completed,
            'progress_percentage' => $percentage,
            'attendance_rate' => $percentage,
        ])->save();
    }

    /**
     * Get the member who enrolled
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the user who approved this enrollment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if enrollment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if enrollment is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if enrollment is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if enrollment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}