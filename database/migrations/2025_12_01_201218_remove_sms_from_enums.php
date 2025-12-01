<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records with 'sms' to 'email' before modifying enum
        DB::table('members')->where('preferred_contact', 'sms')->update(['preferred_contact' => 'email']);
        DB::table('messages')->where('channel', 'sms')->update(['channel' => 'email']);
        DB::table('message_logs')->where('channel', 'sms')->update(['channel' => 'email']);

        // Modify members table enum
        DB::statement("ALTER TABLE members MODIFY COLUMN preferred_contact ENUM('email', 'call') DEFAULT 'email'");

        // Modify messages table enum
        DB::statement("ALTER TABLE messages MODIFY COLUMN channel ENUM('email') DEFAULT 'email'");

        // Modify message_logs table enum
        DB::statement("ALTER TABLE message_logs MODIFY COLUMN channel ENUM('email')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to include SMS option
        DB::statement("ALTER TABLE members MODIFY COLUMN preferred_contact ENUM('sms', 'email', 'call') DEFAULT 'sms'");
        DB::statement("ALTER TABLE messages MODIFY COLUMN channel ENUM('sms', 'email') DEFAULT 'email'");
        DB::statement("ALTER TABLE message_logs MODIFY COLUMN channel ENUM('sms', 'email')");
    }
};
