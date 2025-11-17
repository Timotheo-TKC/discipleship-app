#!/bin/bash
# Gmail SMTP Configuration Script

echo "=========================================="
echo "Gmail SMTP Configuration Helper"
echo "=========================================="
echo ""
echo "This script will help you configure Gmail SMTP."
echo ""
echo "STEP 1: Generate Gmail App Password"
echo "-----------------------------------"
echo "1. Go to: https://myaccount.google.com/security"
echo "2. Enable '2-Step Verification' (if not already enabled)"
echo "3. Go to: https://myaccount.google.com/apppasswords"
echo "4. Select 'Mail' and 'Other (Custom name)'"
echo "5. Enter: 'Discipleship App'"
echo "6. Click 'Generate'"
echo "7. Copy the 16-character password (remove spaces)"
echo ""
read -p "Have you generated the app password? (y/n): " step1_done

if [ "$step1_done" != "y" ] && [ "$step1_done" != "Y" ]; then
    echo "Please complete Step 1 first, then run this script again."
    exit 1
fi

echo ""
echo "STEP 2: Enter Gmail Configuration"
echo "-----------------------------------"
read -p "Enter your Gmail address: " gmail_address
read -p "Enter your 16-character app password: " app_password
read -p "Enter your APP_URL (e.g., http://localhost:8000 or http://10.1.68.146:8000): " app_url

if [ -z "$gmail_address" ] || [ -z "$app_password" ] || [ -z "$app_url" ]; then
    echo "Error: All fields are required!"
    exit 1
fi

# Backup current .env
echo ""
echo "Creating backup of .env file..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update .env file
echo ""
echo "Updating .env file with Gmail SMTP settings..."

# Use sed to update .env file
sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=smtp|" .env
sed -i "s|^MAIL_HOST=.*|MAIL_HOST=smtp.gmail.com|" .env
sed -i "s|^MAIL_PORT=.*|MAIL_PORT=587|" .env
sed -i "s|^MAIL_USERNAME=.*|MAIL_USERNAME=$gmail_address|" .env
sed -i "s|^MAIL_PASSWORD=.*|MAIL_PASSWORD=$app_password|" .env
sed -i "s|^MAIL_ENCRYPTION=.*|MAIL_ENCRYPTION=tls|" .env
sed -i "s|^MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"$gmail_address\"|" .env
sed -i "s|^APP_URL=.*|APP_URL=$app_url|" .env

echo ""
echo "✅ Configuration updated!"
echo ""
echo "Clearing configuration cache..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "=========================================="
echo "Configuration Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test email sending with: php artisan tinker"
echo "   Then run: Mail::raw('Test', function(\$m) { \$m->to('your-email@gmail.com')->subject('Test'); });"
echo ""
echo "2. Try registering a new user to test verification emails"
echo ""
echo "3. Check your Gmail inbox (and spam folder) for emails"
echo ""

