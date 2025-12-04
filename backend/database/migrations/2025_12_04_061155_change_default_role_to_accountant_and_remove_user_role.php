<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration:
     * 1. Updates existing users with 'user' role to 'accountant' role
     * 2. Changes the default role from 'user' to 'accountant'
     *
     * The 'user' role is being removed from the system.
     * Only 'admin' and 'accountant' roles are now supported.
     */
    public function up(): void
    {
        // Update existing users with 'user' role to 'accountant'
        DB::table('users')
            ->where('role', 'user')
            ->update(['role' => 'accountant']);

        // Change the default value of the role column
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('accountant')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });

        // Note: We cannot restore which users were originally 'user' role
        // This is a one-way migration
    }
};
