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
        Schema::table('mentorships', function (Blueprint $table) {
            $table->date('end_date')->nullable();
            $table->enum('meeting_frequency', ['weekly', 'biweekly', 'monthly'])->nullable();
            $table->timestamp('completed_at')->nullable();

            // Add indexes
            $table->index(['end_date']);
            $table->index(['meeting_frequency']);
            $table->index(['completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropIndex(['end_date']);
            $table->dropIndex(['meeting_frequency']);
            $table->dropIndex(['completed_at']);

            $table->dropColumn([
                'end_date',
                'meeting_frequency',
                'completed_at',
            ]);
        });
    }
};
