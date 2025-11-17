<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_type')->comment('e.g., class_reminder, welcome, follow_up');
            $table->text('template')->comment('Message template with variables like {{name}}, {{class_title}}');
            $table->enum('channel', ['sms', 'email'])->default('email');
            $table->datetime('scheduled_at')->index();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft');
            $table->json('payload')->nullable()->comment('Additional data for template variables');
            $table->datetime('sent_at')->nullable();
            $table->json('metadata')->nullable()->comment('Additional metadata like sender info');
            $table->timestamps();

            // Additional indexes for performance
            $table->index(['message_type']);
            $table->index(['channel']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
