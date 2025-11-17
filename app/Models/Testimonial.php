<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'class_id',
        'mentorship_id',
        'content',
        'type',
        'status',
        'rating',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the member who wrote this testimonial
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the class this testimonial is for (if any)
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(DiscipleshipClass::class, 'class_id');
    }

    /**
     * Get the mentorship this testimonial is for (if any)
     */
    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(Mentorship::class, 'mentorship_id');
    }

    /**
     * Get the user who approved this testimonial
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if testimonial is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if testimonial is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if testimonial is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}