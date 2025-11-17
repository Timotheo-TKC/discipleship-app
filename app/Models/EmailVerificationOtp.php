<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmailVerificationOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp',
        'expires_at',
        'used',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a 6-digit OTP
     */
    public static function generateOtp(): string
    {
        return str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for a user
     */
    public static function createForUser(User $user): self
    {
        // Invalidate any existing unused OTPs for this user
        self::where('user_id', $user->id)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->update(['used' => true]);

        // Generate new OTP
        $otp = self::generateOtp();

        return self::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10), // OTP valid for 10 minutes
            'used' => false,
            'attempts' => 0,
        ]);
    }

    /**
     * Verify if the OTP is valid
     */
    public function isValid(string $otp): bool
    {
        if ($this->used) {
            return false;
        }

        if ($this->expires_at->isPast()) {
            return false;
        }

        if ($this->otp !== $otp) {
            $this->increment('attempts');
            return false;
        }

        return true;
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Get the user this OTP belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if OTP has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired(): void
    {
        self::where('expires_at', '<', now())
            ->orWhere('created_at', '<', now()->subDays(1))
            ->delete();
    }
}
