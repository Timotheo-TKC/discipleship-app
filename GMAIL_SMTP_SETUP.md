# Gmail SMTP Configuration Guide

## Setup Gmail for Email Verification

### Step 1: Generate Gmail App Password

1. Go to your Google Account: https://myaccount.google.com
2. Navigate to **Security** → **2-Step Verification** (must be enabled)
3. Scroll down to **App passwords**
4. Select **Mail** and your device
5. Click **Generate**
6. Copy the 16-character password (you'll need this)

### Step 2: Update .env File

Edit your `.env` file with Gmail SMTP settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# Important: Update APP_URL to match your actual domain
APP_URL=http://localhost:8000
# Or for production: APP_URL=https://yourdomain.com
```

### Step 3: Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test Email Sending

```bash
# Test via tinker
php artisan tinker
```

```php
Mail::raw('Test email', function($message) {
    $message->to('your-test-email@gmail.com')
            ->subject('Test Email');
});
```

## Important Notes

1. **App Password Required**: Gmail requires app-specific passwords (not your regular password)
2. **2-Step Verification**: Must be enabled on your Google account
3. **APP_URL**: Must match your actual domain for verification links to work
4. **Rate Limits**: Gmail has sending limits (~500 emails/day for free accounts)

## Verification Flow

1. User registers → Verification email sent to their Gmail
2. User clicks link in email → Redirected to `/verify-email/{id}/{hash}`
3. System verifies email → User marked as verified
4. User redirected to dashboard → Can access all features

## Troubleshooting

**Problem**: Emails not sending
- Verify app password is correct (16 characters, no spaces)
- Check 2-step verification is enabled
- Verify port 587 and encryption TLS
- Check Gmail account for security alerts

**Problem**: Verification link doesn't work
- Ensure APP_URL matches your actual domain
- Check link hasn't expired (60 minutes default)
- Verify user is logged in (or logout and login again)

**Problem**: "Connection timeout"
- Check firewall allows port 587
- Try port 465 with SSL encryption instead
- Verify Gmail account isn't locked

