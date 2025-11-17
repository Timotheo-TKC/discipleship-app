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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('full_name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->date('date_of_conversion')->index();
            $table->enum('preferred_contact', ['sms', 'email', 'call'])->default('sms');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Additional indexes for performance
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
