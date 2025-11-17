# Testing Class Reminder Email Functionality

## Overview

The class reminder system sends automated email notifications to enrolled members about upcoming class sessions. This document explains how the system works and how to test it.

## How It Works

### 1. Notification Class
- **File**: `app/Notifications/ClassReminderNotification.php`
- Sends formatted email reminders to members
- Includes session details: date, time, topic, location, Google Meet link (if available)

### 2. Console Command
- **File**: `app/Console/Commands/SendClassRemindersCommand.php`
- Command: `php artisan classes:send-reminders`
- Finds upcoming sessions and sends reminders to enrolled members
- Supports dry-run mode for testing

### 3. Reminder Logic
- Finds sessions scheduled for a specific date (default: 1 day before)
- Only sends to members with approved enrollments
- Only sends to members with valid email addresses
- Logs all sending attempts

## Setup for Testing

### 1. Configure Mail Settings

Make sure your `.env` file has mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourchurch.org"
MAIL_FROM_NAME="${APP_NAME}"
```

For local testing with Mailhog:
```bash
# Install Mailhog (if not already installed)
# Or use Laravel Sail which includes Mailhog
```

### 2. Create Test Data

You need:
- An active class (`is_active = true`)
- At least one approved enrollment in that class
- At least one upcoming session
- Member with a valid email address linked to a User account

## Testing Methods

### Method 1: Manual Command Testing (Recommended)

#### Step 1: Create Test Data

```bash
# Use Tinker to create test data
php artisan tinker
```

```php
// Create or get a class
$class = \App\Models\DiscipleshipClass::first();
// Or create one:
$class = \App\Models\DiscipleshipClass::create([
    'title' => 'Test Class',
    'description' => 'Testing reminders',
    'mentor_id' => \App\Models\User::where('role', 'pastor')->first()->id,
    'capacity' => 10,
    'duration_weeks' => 4,
    'start_date' => now(),
    'end_date' => now()->addWeeks(4),
    'is_active' => true,
]);

// Create a session tomorrow
$session = \App\Models\ClassSession::create([
    'class_id' => $class->id,
    'session_date' => now()->addDay(),
    'topic' => 'Test Session Topic',
    'location' => 'Main Hall',
]);

// Get or create a member with email
$member = \App\Models\Member::first();
// Ensure member has a user with email
$user = $member->user;
if (!$user || !$user->email) {
    $user = \App\Models\User::create([
        'name' => $member->full_name,
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'member',
    ]);
    $member->update(['user_id' => $user->id]);
}

// Create approved enrollment
\App\Models\ClassEnrollment::create([
    'class_id' => $class->id,
    'member_id' => $member->id,
    'status' => 'approved',
    'enrolled_at' => now(),
]);
```

#### Step 2: Test with Dry Run (No emails sent)

```bash
php artisan classes:send-reminders --dry-run
```

Expected output:
```
Looking for sessions scheduled for 2025-XX-XX
Found 1 session(s) scheduled for 2025-XX-XX

Processing: Test Class - Test Session Topic
Date: 2025-XX-XX
Enrolled members: 1
  [DRY RUN] Would send reminder to: test@example.com (Member Name)
```

#### Step 3: Send Real Reminders

```bash
php artisan classes:send-reminders
```

Check Mailhog or your mail client to see the emails.

#### Step 4: Test Different Days Before

```bash
# Send reminders 2 days before
php artisan classes:send-reminders --days=2

# Send reminders 3 days before
php artisan classes:send-reminders --days=3
```

### Method 2: Automated Testing via Unit Tests

Create a test file:

```bash
php artisan make:test ClassReminderTest
```

Example test:

```php
<?php

namespace Tests\Feature;

use App\Models\ClassEnrollment;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\User;
use App\Notifications\ClassReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClassReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_command_sends_notifications()
    {
        Notification::fake();

        // Create test data
        $user = User::factory()->create(['role' => 'member', 'email' => 'test@example.com']);
        $member = Member::factory()->create(['user_id' => $user->id, 'email' => $user->email]);
        $pastor = User::factory()->create(['role' => 'pastor']);
        
        $class = DiscipleshipClass::factory()->create([
            'mentor_id' => $pastor->id,
            'is_active' => true,
        ]);

        ClassEnrollment::factory()->create([
            'class_id' => $class->id,
            'member_id' => $member->id,
            'status' => 'approved',
        ]);

        $session = ClassSession::factory()->create([
            'class_id' => $class->id,
            'session_date' => now()->addDay(),
            'topic' => 'Test Session',
        ]);

        // Run command
        $this->artisan('classes:send-reminders')
            ->expectsOutput('Found')
            ->assertExitCode(0);

        // Assert notification was sent
        Notification::assertSentTo($user, ClassReminderNotification::class);
    }

    public function test_reminder_includes_session_details()
    {
        $notification = new ClassReminderNotification(
            ClassSession::factory()->create([
                'topic' => 'Test Topic',
                'session_date' => now()->addDay(),
            ]),
            1
        );

        $user = User::factory()->create(['email' => 'test@example.com']);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Test Topic', $mail->subject);
    }
}
```

### Method 3: Schedule via Cron (Production)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Send class reminders daily at 9 AM
    $schedule->command('classes:send-reminders --days=1')
        ->dailyAt('09:00')
        ->timezone('Africa/Nairobi'); // Adjust to your timezone
    
    // Optional: Send 3-day advance reminders
    $schedule->command('classes:send-reminders --days=3')
        ->dailyAt('09:00');
}
```

## Common Issues & Troubleshooting

### Issue: "No upcoming sessions found"
**Solution**: Ensure sessions have `session_date` set correctly and classes are `is_active = true`

### Issue: "Skipping member - no email address"
**Solution**: Member's associated User must have a valid email address

### Issue: Emails not sending
**Solutions**:
1. Check mail configuration in `.env`
2. Verify Mailhog is running: `http://localhost:8025`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test mail sending: `php artisan tinker` then `Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });`

### Issue: Wrong recipients
**Solution**: Verify enrollments have `status = 'approved'` and members are correctly linked to users

## Testing Checklist

- [ ] Mail configuration is correct
- [ ] Test class created and active
- [ ] Test session created for tomorrow
- [ ] Test member has approved enrollment
- [ ] Test member has valid email
- [ ] Dry run shows correct output
- [ ] Real command sends emails
- [ ] Emails contain correct session details
- [ ] Multiple days before work correctly
- [ ] Only approved enrollments receive emails
- [ ] Logs are created correctly

## Email Content Verification

The reminder email should include:
- Greeting with member's full name
- Class title
- Session topic
- Session date and time
- Location (if set)
- Google Meet link (if set)
- Additional notes (if set)
- Mentor signature

## Next Steps

1. Set up automated scheduling via cron
2. Customize email templates as needed
3. Add more reminder timing options
4. Implement unsubscribe functionality (if needed)
5. Add email tracking and analytics

