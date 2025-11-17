# Fix Gmail SMTP - Step by Step Guide

## ❌ Current Problem

Your `.env` file is still configured with:
```env
MAIL_MAILER=log        # ← This is the problem! It's logging, not sending
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

**This means emails are being written to logs, NOT sent to Gmail inboxes!**

## ✅ Solution: Configure Gmail SMTP

### Option 1: Use the Automated Script (Easiest)

```bash
./configure-gmail-smtp.sh
```

The script will guide you through the process interactively.

### Option 2: Manual Configuration

#### Step 1: Generate Gmail App Password

1. **Go to Google Account Security:**
   - Visit: https://myaccount.google.com/security

2. **Enable 2-Step Verification** (Required):
   - Scroll to "2-Step Verification"
   - Click "Get Started" and follow the prompts
   - Use your phone to verify

3. **Generate App Password:**
   - After enabling 2-Step, go to: https://myaccount.google.com/apppasswords
   - Select "Mail" from dropdown
   - Select "Other (Custom name)"
   - Enter: `Discipleship App`
   - Click "Generate"
   - **Copy the 16-character password** (looks like: `abcd efgh ijkl mnop`)
   - Remove spaces: `abcdefghijklmnop`

#### Step 2: Update .env File

Edit your `.env` file and change these lines:

```env
# Change from 'log' to 'smtp'
MAIL_MAILER=smtp

# Gmail SMTP Settings
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# IMPORTANT: Update APP_URL to match your server
APP_URL=http://localhost:8000
# Or if accessible via IP: APP_URL=http://10.1.68.146:8000
```

**Example:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=myaccount@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="myaccount@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
APP_URL=http://localhost:8000
```

#### Step 3: Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

#### Step 4: Test Email Sending

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email from Discipleship App', function($message) {
    $message->to('your-test-email@gmail.com')
            ->subject('Test Email');
});

echo "Email sent! Check your inbox (and spam folder).";
```

#### Step 5: Test Registration Flow

1. Register a new user with a Gmail address
2. Check the Gmail inbox for verification email
3. Click the verification link
4. Should be redirected to dashboard

## 🔍 Troubleshooting

### Problem: Still not receiving emails

**Check 1: Verify Configuration**
```bash
php artisan tinker
```
```php
echo 'Driver: ' . config('mail.default');
echo 'Host: ' . config('mail.mailers.smtp.host');
echo 'Port: ' . config('mail.mailers.smtp.port');
echo 'Username: ' . config('mail.mailers.smtp.username');
echo 'Password: ' . (config('mail.mailers.smtp.password') ? 'SET' : 'NOT SET');
```

**Check 2: Check Logs for Errors**
```bash
tail -50 storage/logs/laravel.log | grep -i "mail\|smtp\|error"
```

**Check 3: Test SMTP Connection**
```bash
php artisan tinker
```
```php
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

try {
    $transport = new EsmtpTransport(
        config('mail.mailers.smtp.host'),
        config('mail.mailers.smtp.port')
    );
    $transport->setUsername(config('mail.mailers.smtp.username'));
    $transport->setPassword(config('mail.mailers.smtp.password'));
    
    echo "SMTP connection test successful!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Common Issues

**1. "Authentication failed"**
- ✅ Ensure you're using an **app password**, not your regular Gmail password
- ✅ Verify 2-Step Verification is enabled
- ✅ Check password has no spaces

**2. "Connection timeout"**
- ✅ Check firewall allows port 587
- ✅ Try port 465 with SSL instead:
  ```env
  MAIL_PORT=465
  MAIL_ENCRYPTION=ssl
  ```

**3. "Emails going to spam"**
- ✅ Check spam folder
- ✅ Use a proper `MAIL_FROM_ADDRESS` matching your Gmail
- ✅ Consider adding SPF/DKIM records for your domain (if using custom domain)

**4. "APP_URL mismatch"**
- ✅ Ensure `APP_URL` in `.env` matches where users access your app
- ✅ If using `localhost:8000`, keep it as `http://localhost:8000`
- ✅ If accessible via IP, use `http://10.1.68.146:8000` (or your IP)

### Verify .env Changes

After updating `.env`, verify with:
```bash
cat .env | grep "^MAIL_"
cat .env | grep "^APP_URL="
```

You should see:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
APP_URL=http://localhost:8000
```

## ✅ Quick Test Commands

```bash
# 1. Check current mail config
php artisan tinker --execute="echo config('mail.default');"

# 2. Send test email
php artisan tinker
# Then: Mail::raw('Test', fn($m) => $m->to('email@gmail.com')->subject('Test'));

# 3. Check logs for errors
tail -f storage/logs/laravel.log
```

## 📝 Summary

1. ✅ Generate Gmail app password (16 characters)
2. ✅ Update `.env` file with Gmail SMTP settings
3. ✅ Change `MAIL_MAILER=log` to `MAIL_MAILER=smtp`
4. ✅ Set Gmail credentials in `.env`
5. ✅ Clear config cache: `php artisan config:clear`
6. ✅ Test email sending
7. ✅ Test user registration and verification

**Once configured, verification emails will be sent to real Gmail inboxes!**

