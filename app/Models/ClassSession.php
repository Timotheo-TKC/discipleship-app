<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    use HasFactory;
    /**
     * The table name (if different from default)
     */
    protected $table = 'class_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'class_id',
        'session_date',
        'topic',
        'notes',
        'location',
        'google_meet_link',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'session_date' => 'date',
    ];

    /**
     * Get the class this session belongs to
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(DiscipleshipClass::class, 'class_id');
    }

    /**
     * Get the user who created this session
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all attendance records for this session
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_session_id');
    }

    /**
     * Get present attendance for this session
     */
    public function presentAttendance(): HasMany
    {
        return $this->attendance()->where('status', 'present');
    }

    /**
     * Get attendance count for this session
     */
    public function getAttendanceCount(): int
    {
        return $this->attendance()->count();
    }

    /**
     * Get present count for this session
     */
    public function getPresentCount(): int
    {
        return $this->presentAttendance()->count();
    }

    /**
     * Get attendance rate for this session
     */
    public function getAttendanceRate(): float
    {
        $total = $this->getAttendanceCount();
        if ($total === 0) {
            return 0.0;
        }

        $present = $this->getPresentCount();

        return round(($present / $total) * 100, 2);
    }

    /**
     * Check if session is in the future
     */
    public function isUpcoming(): bool
    {
        return $this->session_date > now()->toDateString();
    }

    /**
     * Check if session is today
     */
    public function isToday(): bool
    {
        return $this->session_date === now()->toDateString();
    }

    /**
     * Check if session is in the past
     */
    public function isPast(): bool
    {
        return $this->session_date < now()->toDateString();
    }
}
