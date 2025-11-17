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
        Schema::create('class_content_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_content_id')->constrained('class_contents')->onDelete('cascade');
            $table->foreignId('class_enrollment_id')->constrained('class_enrollments')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['class_content_id', 'class_enrollment_id'], 'content_enrollment_unique');
            $table->index(['class_enrollment_id', 'is_completed'], 'content_progress_completion_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_content_progress');
    }
};

