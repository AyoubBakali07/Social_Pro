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
        // Update existing status values to capitalized versions
        DB::statement("UPDATE agencies SET status = 'Active' WHERE status = 'active'");
        DB::statement("UPDATE agencies SET status = 'Inactive' WHERE status = 'inactive'");
        DB::statement("UPDATE agencies SET status = 'Pending' WHERE status = 'pending'");
        
        // Change the enum values
        DB::statement("ALTER TABLE agencies MODIFY COLUMN status ENUM('Active', 'Inactive', 'Pending')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to lowercase values
        DB::statement("UPDATE agencies SET status = 'active' WHERE status = 'Active'");
        DB::statement("UPDATE agencies SET status = 'inactive' WHERE status = 'Inactive'");
        DB::statement("UPDATE agencies SET status = 'pending' WHERE status = 'Pending'");
        
        // Change the enum values back
        DB::statement("ALTER TABLE agencies MODIFY COLUMN status ENUM('active', 'inactive', 'pending')");
    }
};
