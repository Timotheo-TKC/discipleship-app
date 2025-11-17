# Setup Gmail SMTP for Email Verification

## Quick Setup Steps

### 1. Enable 2-Step Verification on Gmail

1. Go to: https://myaccount.google.com/security
2. Enable **2-Step Verification** (if not already enabled)

### 2. Generate App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Select **Mail** and **Other (Custom name)**
3. Enter name: "Discipleship App"
4. Click **Generate**
5. Copy the 16-character password (example: `abcd efgh ijkl mnop`)

### 3. Update .env File

Edit `/home/chumot/discipleship-app/.env`:

```env
# Change from 'log' to 'smtp'
MAIL_MAILER=smtp

# Gmail SMTP settings
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# IMPORTANT: Update APP_URL to match your server
APP_URL=http://10.1.68.146:8000
# Or if using localhost: APP_URL=http://localhost:8000
```

**Replace:**
- `your-gmail@gmail.com` - Your actual Gmail address
- `abcd efgh ijkl mnop` - Your 16-character app password (remove spaces if any)
- `APP_URL` - Should match where your server is accessible

### 4. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Test Email Sending

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email from Discipleship App', function($message) {
    $message->to('your-test-email@gmail.com')
            ->subject('Test Email');
});

echo "Email sent! Check your inbox.";
```

## Email Verification Flow

1. **User Registers** → Verification email sent to their Gmail
2. **User Checks Email** → Finds verification link in inbox
3. **User Clicks Link** → Redirected to verification route
4. **System Verifies** → Email marked as verified, user logged in
5. **Redirect to Dashboard** → User can now access all features

## Important Notes

- **App Password Required**: Use app password, NOT your regular Gmail password
- **APP_URL Must Match**: The URL in verification links uses APP_URL, must be correct
- **2-Step Verification**: Must be enabled to generate app passwords
- **Rate Limits**: Gmail free accounts have sending limits (~500/day)

## Troubleshooting

**Emails not received?**
- Check spam folder
- Verify app password is correct
- Check Gmail account for security alerts
- Verify SMTP settings are correct

**Verification link doesn't work?**
- Ensure APP_URL matches your actual domain/IP
- Check if link expired (default: 60 minutes)
- Verify user email matches the verification link

**Connection errors?**
- Verify port 587 is open
- Check firewall settings
- Try port 465 with SSL encryption
- Ensure 2-step verification is enabled

