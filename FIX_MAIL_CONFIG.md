# Fix Mail Configuration Error

## Current Problem

The application is trying to connect to a mail server at `127.0.0.1:2525` but getting "Connection refused". This happens during user registration when sending email verification.

## Quick Fix Options

### Option 1: Use Log Driver (Easiest - No Mail Server Needed)

Change your `.env` file:

```env
MAIL_MAILER=log
```

This will write all emails to `storage/logs/laravel.log` instead of sending them. Perfect for local development and testing.

**To view emails:**
```bash
tail -f storage/logs/laravel.log | grep -A 50 "Message-ID"
```

### Option 2: Use Array Driver (For Testing)

```env
MAIL_MAILER=array
```

This stores emails in memory during the request. Good for automated tests.

### Option 3: Fix Mailhog Configuration

If you want to use Mailhog for testing:

1. **Install Mailhog** (if not installed):
```bash
# On macOS
brew install mailhog

# On Linux (using Go)
go install github.com/mailhog/MailHog@latest

# Or download from: https://github.com/mailhog/MailHog
```

2. **Start Mailhog**:
```bash
mailhog
# Or: ~/go/bin/MailHog (if installed via Go)
```

3. **Update .env** (Mailhog uses port 1025, not 2525):
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

4. **Access Mailhog Web UI**: `http://localhost:8025`

### Option 4: Use Real SMTP (Production)

For production, configure real SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Recommended for Development

**Use the `log` driver** - it's the simplest and requires no setup:

```env
MAIL_MAILER=log
```

Then clear config cache:
```bash
php artisan config:clear
```

## Test After Fixing

1. Try registering a new user
2. Check the logs: `tail -f storage/logs/laravel.log`
3. You should see the email content logged instead of an error

## For Class Reminders

After fixing mail config, you can test class reminders:

```bash
# The reminder command will use the same mail configuration
php artisan classes:send-reminders --dry-run
php artisan classes:send-reminders
```

With `log` driver, reminder emails will be written to the log file.

