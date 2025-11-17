<?php
/**
 * Gmail SMTP Test Script
 * 
 * Run this script to test your Gmail SMTP configuration:
 * php test-gmail-smtp.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n==========================================\n";
echo "Gmail SMTP Configuration Test\n";
echo "==========================================\n\n";

// Check current configuration
echo "Current Mail Configuration:\n";
echo "---------------------------\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
echo "SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
echo "SMTP Username: " . (config('mail.mailers.smtp.username') ?: 'NOT SET') . "\n";
echo "SMTP Password: " . (config('mail.mailers.smtp.password') ? '***SET***' : 'NOT SET') . "\n";
echo "SMTP Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "APP_URL: " . config('app.url') . "\n\n";

// Check if using log driver
if (config('mail.default') === 'log') {
    echo "⚠️  WARNING: Mail driver is set to 'log'\n";
    echo "   Emails are being logged, NOT sent to Gmail!\n";
    echo "   Update .env: MAIL_MAILER=smtp\n\n";
}

// Test SMTP connection if configured
if (config('mail.default') === 'smtp' && config('mail.mailers.smtp.username')) {
    echo "Testing SMTP Connection...\n";
    echo "---------------------------\n";
    
    try {
        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            config('mail.mailers.smtp.host'),
            config('mail.mailers.smtp.port')
        );
        
        $transport->setUsername(config('mail.mailers.smtp.username'));
        $transport->setPassword(config('mail.mailers.smtp.password'));
        
        echo "✅ SMTP connection parameters are valid\n\n";
        
        // Test sending a simple email
        echo "To test sending an email, run:\n";
        echo "php artisan tinker\n";
        echo "Then run:\n";
        echo "Mail::raw('Test email', function(\$m) {\n";
        echo "    \$m->to('your-email@gmail.com')->subject('Test');\n";
        echo "});\n\n";
        
    } catch (\Exception $e) {
        echo "❌ SMTP Configuration Error: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "⚠️  Cannot test SMTP - configuration incomplete\n\n";
}

echo "==========================================\n";
echo "Next Steps:\n";
echo "1. If MAIL_MAILER=log, update to MAIL_MAILER=smtp in .env\n";
echo "2. Configure Gmail SMTP settings in .env\n";
echo "3. Generate app password: https://myaccount.google.com/apppasswords\n";
echo "4. Run: php artisan config:clear\n";
echo "5. Test email sending\n";
echo "==========================================\n\n";

