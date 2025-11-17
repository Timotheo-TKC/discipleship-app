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
        Schema::table('classes', function (Blueprint $table) {
            // Drop the index first, then the column
            $table->dropIndex(['schedule_rule']);
            $table->dropColumn('schedule_rule');

            // Add new scheduling columns
            $table->enum('schedule_type', ['weekly', 'biweekly', 'monthly', 'custom'])->default('weekly');
            $table->enum('schedule_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->time('schedule_time')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);

            // Update indexes
            $table->index(['schedule_type']);
            $table->index(['is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'schedule_type',
                'schedule_day',
                'schedule_time',
                'start_date',
                'end_date',
                'location',
                'is_active',
            ]);

            // Add back old column
            $table->string('schedule_rule')->comment('RRULE-like string for recurring schedule');
        });
    }
};
