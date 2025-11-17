<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'coordinator')
            ->update(['role' => 'mentor']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','pastor','mentor','member') DEFAULT 'member'");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA writable_schema = 1');
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin','pastor','coordinator','member'\", \"'admin','pastor','mentor','member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin', 'pastor', 'coordinator', 'member'\", \"'admin', 'pastor', 'mentor', 'member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin','pastor','member'\", \"'admin','pastor','mentor','member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin', 'pastor', 'member'\", \"'admin', 'pastor', 'mentor', 'member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement('PRAGMA writable_schema = 0');
            DB::statement('VACUUM');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','pastor','member') DEFAULT 'member'");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA writable_schema = 1');
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin','pastor','mentor','member'\", \"'admin','pastor','member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin', 'pastor', 'mentor', 'member'\", \"'admin', 'pastor', 'member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin','pastor','mentor','member'\", \"'admin','pastor','member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'admin', 'pastor', 'mentor', 'member'\", \"'admin', 'pastor', 'member'\") WHERE type = 'table' AND name = 'users'");
            DB::statement('PRAGMA writable_schema = 0');
            DB::statement('VACUUM');
        }
    }
};

