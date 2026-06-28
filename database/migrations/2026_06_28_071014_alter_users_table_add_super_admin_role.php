<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 'super_admin' as a valid role value in the users table.
     * The role column is a plain string so no enum change needed —
     * this migration is a no-op on the schema but documents the intent.
     */
    public function up(): void
    {
        // Role column is already a varchar — super_admin is already a valid value.
        // This migration serves as documentation and can be used to seed a default super_admin.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
