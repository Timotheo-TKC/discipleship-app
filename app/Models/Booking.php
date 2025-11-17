<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'mentor_id',
        'booking_date',
        'duration_minutes',
        'status',
        'location',
        'notes',
        'member_notes',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Get the member who made the booking
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the mentor for this booking
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /**
     * Get the user who confirmed this booking
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Check if booking is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get end time for booking
     */
    public function getEndTime()
    {
        return $this->booking_date->copy()->addMinutes($this->duration_minutes);
    }
}