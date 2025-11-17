<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\DiscipleshipClass;
use App\Models\Mentorship;
use App\Models\Booking;
use App\Models\ClassSession;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Get all coordinator users
        $coordinators = User::where('role', 'coordinator')->get();
        
        if ($coordinators->isEmpty()) {
            // No coordinators exist, just update the enum
            $this->updateRoleEnum();
            return;
        }

        // Step 2: Get first available pastor (or admin if no pastor exists) to reassign classes/mentorships
        $reassignTo = User::whereIn('role', ['pastor', 'admin'])
            ->orderByRaw("CASE WHEN role = 'pastor' THEN 0 ELSE 1 END")
            ->first();

        if (!$reassignTo) {
            // No pastor or admin exists - create a default pastor
            $reassignTo = User::create([
                'name' => 'System Pastor',
                'email' => 'pastor@discipleship.local',
                'password' => bcrypt('password'),
                'role' => 'pastor',
                'email_verified_at' => now(),
            ]);
        }

        // Step 3: Reassign all classes from coordinators to the pastor/admin
        foreach ($coordinators as $coordinator) {
            DiscipleshipClass::where('mentor_id', $coordinator->id)
                ->update(['mentor_id' => $reassignTo->id]);
        }

        // Step 4: Reassign all mentorships from coordinators to the pastor/admin
        foreach ($coordinators as $coordinator) {
            Mentorship::where('mentor_id', $coordinator->id)
                ->update(['mentor_id' => $reassignTo->id]);
        }

        // Step 5: Reassign all bookings from coordinators to the pastor/admin
        foreach ($coordinators as $coordinator) {
            Booking::where('mentor_id', $coordinator->id)
                ->update(['mentor_id' => $reassignTo->id]);
        }

        // Step 6: Reassign all class sessions created by coordinators
        foreach ($coordinators as $coordinator) {
            \App\Models\ClassSession::where('created_by', $coordinator->id)
                ->update(['created_by' => $reassignTo->id]);
        }

        // Step 7: Delete all coordinator user accounts
        foreach ($coordinators as $coordinator) {
            $coordinator->delete();
        }

        // Step 8: Update the role enum to remove 'coordinator'
        $this->updateRoleEnum();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add coordinator back to enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pastor', 'coordinator', 'member') DEFAULT 'member'");
    }

    /**
     * Update role enum to remove coordinator
     */
    private function updateRoleEnum(): void
    {
        // Check if using MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pastor', 'member') DEFAULT 'member'");
        } else {
            // For SQLite (testing), we'll use a different approach
            // SQLite doesn't support enum constraints, so we just update existing values
            // This is handled by the application layer in Laravel
        }
    }
};
