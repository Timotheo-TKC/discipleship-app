# Email Verification Guide

## Current Setup

The system is configured to use the `log` mail driver, which means **emails are written to log files instead of being sent to actual email addresses**.

## How Email Verification Works

### 1. During Registration
- When a user registers, Laravel automatically fires the `Registered` event
- This triggers `SendEmailVerificationNotification` listener
- The verification email is "sent" (logged to `storage/logs/laravel.log`)
- User is redirected to dashboard but needs to verify email

### 2. Email Verification Page
- Accessible at: `/verify-email`
- Shows a message asking user to verify email
- Has a "Resend Verification Email" button

### 3. With Log Driver (Current Setup)

Since emails aren't actually sent, the verification page now shows:
- **Direct verification link** (in development mode)
- Click the button or copy the URL to verify
- Link is valid for 60 minutes

## How to Verify Email

### Method 1: Use the Verification Page (Easiest)

1. After registration, go to `/verify-email`
2. You'll see a yellow box with a "Verify Email Address" button
3. Click the button to verify immediately
4. Or copy the URL and paste it in your browser

### Method 2: Extract from Logs

```bash
# Find the latest verification link in logs
tail -500 storage/logs/laravel.log | grep -oP 'verify-email/[^"<]+' | tail -1

# Or search for the full URL
grep -oP 'http://localhost:8000/verify-email/[^"<]+' storage/logs/laravel.log | tail -1
```

### Method 3: Generate Link via Tinker

```bash
php artisan tinker
```

```php
$user = \App\Models\User::whereNull('email_verified_at')->first();
$url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
    'verification.verify',
    now()->addMinutes(60),
    ['id' => $user->id, 'hash' => sha1($user->email)]
);
echo $url;
```

## Testing Email Verification

### Test Registration Flow:
1. Register a new user
2. Should be redirected to dashboard
3. Should see verification notice
4. Go to `/verify-email`
5. Click "Verify Email Address" button
6. Should redirect to dashboard with `?verified=1`

### Test Resend Functionality:
1. Go to `/verify-email` while logged in
2. Click "Resend Verification Email"
3. Should see success message
4. Should see verification link displayed (in log mode)

## For Production

When deploying to production:

1. **Change mail driver** in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

2. **Remove development verification link display** - The verification link box will automatically disappear when not using `log` driver

3. **Test with real email** before going live

## Troubleshooting

### Issue: Verification link expired
**Solution**: Click "Resend Verification Email" to get a new link (valid for 60 minutes)

### Issue: Link doesn't work
**Solutions**:
- Ensure you're logged in as the same user who registered
- Check if link has expired (60 minute limit)
- Generate a new link using resend button

### Issue: "Email already verified"
**Solution**: User is already verified - check `email_verified_at` field in database

## Verification Logic Summary

✅ **Working correctly:**
- Emails are generated and logged
- Verification links are created
- Resend functionality works
- Verification process works

📧 **Email Delivery:**
- In development: Links shown on page (log driver)
- In production: Links sent via email (SMTP driver)

## Quick Commands

```bash
# Check unverified users
php artisan tinker --execute="echo \App\Models\User::whereNull('email_verified_at')->count() . ' unverified users';"

# Generate verification link for first unverified user
php artisan tinker --execute="\$u = \App\Models\User::whereNull('email_verified_at')->first(); if(\$u) echo \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => \$u->id, 'hash' => sha1(\$u->email)]);"
```

