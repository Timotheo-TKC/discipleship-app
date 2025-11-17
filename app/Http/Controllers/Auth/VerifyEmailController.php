<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     * This can be called when user is logged in or from email link (logged out).
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // EmailVerificationRequest automatically authenticates the user if not already authenticated
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            // If user was logged out, login them first
            if (!Auth::check()) {
                Auth::login($user);
            }
            return redirect()->route('dashboard', absolute: false)->with('verified', true);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            
            // If user was logged out, login them now
            if (!Auth::check()) {
                Auth::login($user);
            }
        }

        return redirect()->to(route('dashboard', absolute: false) . '?verified=1')->with('verified', true);
    }
}
