<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Member extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'avatar',
        'date_of_conversion',
        'preferred_contact',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_conversion' => 'date',
        'notes' => 'encrypted', // Encrypt sensitive member notes
    ];

    /**
     * Get the user associated with this member (optional link)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all attendance records for this member
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all mentorships where this member is the mentee
     */
    public function mentorships(): HasMany
    {
        return $this->hasMany(Mentorship::class, 'member_id');
    }

    /**
     * Get all mentorships where this member is the mentee (alias)
     */
    public function mentorshipsAsMentee(): HasMany
    {
        return $this->hasMany(Mentorship::class, 'member_id');
    }

    /**
     * Get all mentorships where this member is the mentor
     */
    public function mentorshipsAsMentor(): HasMany
    {
        return $this->hasMany(Mentorship::class, 'mentor_id');
    }

    /**
     * Get all class enrollments for this member
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    /**
     * Get all bookings for this member
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all testimonials for this member
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        return Storage::url($this->avatar);
    }

    /**
     * Get active mentorship as mentee
     */
    public function activeMentorship(): ?Mentorship
    {
        return $this->mentorshipsAsMentee()
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get current mentor
     */
    public function currentMentor(): ?User
    {
        $mentorship = $this->activeMentorship();

        return $mentorship ? $mentorship->mentor : null;
    }

    /**
     * Get attendance rate for this member
     */
    public function getAttendanceRate(): float
    {
        $totalSessions = $this->attendance()->count();
        if ($totalSessions === 0) {
            return 0.0;
        }

        $presentSessions = $this->attendance()
            ->where('status', 'present')
            ->count();

        return round(($presentSessions / $totalSessions) * 100, 2);
    }

    /**
     * Get attendance rate for a specific class
     */
    public function getAttendanceRateForClass(DiscipleshipClass $class): float
    {
        $sessions = $class->sessions;
        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $attendedSessions = $this->attendance()
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->where('status', 'present')
            ->count();

        return round(($attendedSessions / $sessions->count()) * 100, 2);
    }

    /**
     * Check if member is enrolled in a specific class
     */
    public function isEnrolledInClass(DiscipleshipClass $class): bool
    {
        return $this->enrollments()
            ->where('class_id', $class->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Check if member has pending enrollment for a class
     */
    public function hasPendingEnrollment(DiscipleshipClass $class): bool
    {
        return $this->enrollments()
            ->where('class_id', $class->id)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Check if member has an active enrollment (approved status)
     */
    public function hasActiveEnrollment(): bool
    {
        return $this->enrollments()
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Get current active enrollment
     */
    public function getActiveEnrollment()
    {
        return $this->enrollments()
            ->where('status', 'approved')
            ->with('class')
            ->first();
    }

    /**
     * Check if member can enroll in a new class
     * Only allowed if no active enrollment exists OR if previous enrollment is completed
     */
    public function canEnrollInNewClass(): bool
    {
        // Can enroll if no active enrollment exists
        if (!$this->hasActiveEnrollment()) {
            return true;
        }
        
        // Can enroll if active enrollment is completed
        $activeEnrollment = $this->getActiveEnrollment();
        return $activeEnrollment && $activeEnrollment->isCompleted();
    }

    /**
     * Check if member has completed a class
     */
    public function hasCompletedClass(DiscipleshipClass $class): bool
    {
        return $this->enrollments()
            ->where('class_id', $class->id)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get completed enrollments
     */
    public function completedEnrollments()
    {
        return $this->enrollments()
            ->where('status', 'completed')
            ->with('class');
    }

    /**
     * Check if member has any completed enrollment
     */
    public function hasCompletedAnyClass(): bool
    {
        return $this->completedEnrollments()->exists();
    }

    /**
     * Get preferred contact method
     */
    public function getPreferredContactMethod(): string
    {
        return $this->preferred_contact;
    }

    /**
     * Get contact information for messaging
     */
    public function getContactInfo(): array
    {
        return [
            'email' => $this->email,
            'phone' => $this->phone,
            'preferred' => $this->preferred_contact,
        ];
    }

    /**
     * Scope for members converted within date range
     */
    public function scopeConvertedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_of_conversion', [$startDate, $endDate]);
    }

    /**
     * Scope for members with high attendance rate
     */
    public function scopeHighAttendance($query, $threshold = 80)
    {
        return $query->whereHas('attendance', function ($q) use ($threshold) {
            $q->selectRaw('member_id, COUNT(*) as total_sessions, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_sessions')
                ->groupBy('member_id')
                ->havingRaw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) / COUNT(*) * 100 >= ?', [$threshold]);
        });
    }
}
