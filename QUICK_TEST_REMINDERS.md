# Quick Test Guide: Class Reminder Emails

## Quick Start (5 Minutes)

### 1. Verify Command Exists
```bash
php artisan classes:send-reminders --help
```

### 2. Check Mail Configuration
```bash
# In .env, ensure:
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1  # or mailhog for testing
MAIL_PORT=1025      # Mailhog default port
```

### 3. Create Quick Test Data
```bash
php artisan tinker
```

```php
// Get existing data or create test data
$class = \App\Models\DiscipleshipClass::where('is_active', true)->first();
$member = \App\Models\Member::whereHas('user', function($q) {
    $q->whereNotNull('email');
})->first();

// Create session for tomorrow
$session = \App\Models\ClassSession::create([
    'class_id' => $class->id,
    'session_date' => now()->addDay(),
    'topic' => 'Test Reminder Session',
    'location' => 'Test Location',
]);

// Ensure enrollment exists
\App\Models\ClassEnrollment::firstOrCreate([
    'class_id' => $class->id,
    'member_id' => $member->id,
], [
    'status' => 'approved',
    'enrolled_at' => now(),
]);
```

### 4. Test with Dry Run
```bash
php artisan classes:send-reminders --dry-run
```

### 5. Send Real Reminders
```bash
php artisan classes:send-reminders
```

### 6. Check Results

**If using Mailhog:**
- Open: http://localhost:8025
- You should see the reminder email

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

## Command Options

```bash
# Default: Send reminders for sessions 1 day from now
php artisan classes:send-reminders

# Send reminders 2 days before
php artisan classes:send-reminders --days=2

# Dry run (show what would be sent)
php artisan classes:send-reminders --dry-run

# Combine options
php artisan classes:send-reminders --days=3 --dry-run
```

## What Gets Sent

The email includes:
- ✅ Class title
- ✅ Session topic
- ✅ Date and time
- ✅ Location (if set)
- ✅ Google Meet link (if available)
- ✅ Additional notes (if set)
- ✅ Personalized greeting

## Troubleshooting

**Problem**: "No upcoming sessions found"
- ✅ Check session_date is set correctly
- ✅ Verify class has `is_active = true`
- ✅ Session must be scheduled for exactly `now() + days` days

**Problem**: "Skipping member - no email address"
- ✅ Member must have linked User account
- ✅ User must have email address

**Problem**: Emails not appearing
- ✅ Check MAIL configuration in .env
- ✅ Verify Mailhog is running (if using)
- ✅ Check Laravel logs for errors
- ✅ Verify queue is running (if using queues): `php artisan queue:work`

## Next: Schedule Automatically

Add to `app/Console/Kernel.php` in the `schedule()` method:

```php
$schedule->command('classes:send-reminders --days=1')
    ->dailyAt('09:00');
```

Then run cron:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

