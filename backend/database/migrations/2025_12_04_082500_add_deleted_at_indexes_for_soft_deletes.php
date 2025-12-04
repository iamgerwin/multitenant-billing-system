<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds indexes on deleted_at columns to improve query performance
     * for soft-deleted models. Every query with whereNull('deleted_at')
     * benefits from these indexes.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->index('deleted_at', 'organizations_deleted_at_index');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->index('deleted_at', 'vendors_deleted_at_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('deleted_at', 'invoices_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('organizations_deleted_at_index');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_deleted_at_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_deleted_at_index');
        });
    }
};
