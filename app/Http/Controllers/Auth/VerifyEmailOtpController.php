<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VerifyEmailOtpController extends Controller
{
    /**
     * Show the OTP verification form
     */
    public function show(Request $request)
    {
        // If user is already verified, redirect to dashboard
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('verified', true);
        }

        return view('auth.verify-email-otp');
    }

    /**
     * Verify the OTP code
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => ['Please log in to verify your email.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('verified', true);
        }

        // Find the most recent valid OTP for this user
        $otpRecord = EmailVerificationOtp::where('user_id', $user->id)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            throw ValidationException::withMessages([
                'otp' => ['No valid OTP found. Please request a new one.'],
            ]);
        }

        // Verify the OTP
        if (!$otpRecord->isValid($request->otp)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code. Please check and try again.'],
            ]);
        }

        // Mark OTP as used
        $otpRecord->markAsUsed();

        // Verify the user's email
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('dashboard')->with('verified', true);
    }
}
