<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('class_enrollments', 'completed_lessons')) {
                $table->unsignedInteger('completed_lessons')->default(0)->after('approved_by');
            }

            if (! Schema::hasColumn('class_enrollments', 'progress_percentage')) {
                $table->decimal('progress_percentage', 5, 2)->default(0)->after('completed_lessons');
            }

            if (! Schema::hasColumn('class_enrollments', 'attendance_rate')) {
                $table->decimal('attendance_rate', 5, 2)->default(0)->after('progress_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('class_enrollments', 'attendance_rate')) {
                $table->dropColumn('attendance_rate');
            }

            if (Schema::hasColumn('class_enrollments', 'progress_percentage')) {
                $table->dropColumn('progress_percentage');
            }

            if (Schema::hasColumn('class_enrollments', 'completed_lessons')) {
                $table->dropColumn('completed_lessons');
            }
        });
    }
};

