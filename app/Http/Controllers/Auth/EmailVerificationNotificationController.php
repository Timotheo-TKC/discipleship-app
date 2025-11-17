<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationOtp;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification OTP.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Create and send new OTP
        $otpRecord = EmailVerificationOtp::createForUser($user);
        $user->notify(new EmailVerificationOtpNotification($otpRecord));

        return back()->with('status', 'otp-sent');
    }
}
