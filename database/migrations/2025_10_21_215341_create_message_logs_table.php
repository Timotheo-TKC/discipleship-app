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
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade')->index();
            $table->string('recipient')->comment('Phone number or email address');
            $table->enum('channel', ['sms', 'email']);
            $table->string('result')->comment('success, failed, etc.');
            $table->json('response')->nullable()->comment('API response from SMS/email provider');
            $table->timestamp('created_at');

            // Additional indexes for performance
            $table->index(['recipient']);
            $table->index(['channel']);
            $table->index(['result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
