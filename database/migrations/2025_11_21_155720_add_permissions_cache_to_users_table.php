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
        Schema::table('users', function (Blueprint $table) {
            $table->json('cached_permissions')
                ->nullable()
                ->after('remember_token')
                ->comment('Cached permission map for performance optimization');

            $table->timestamp('permissions_cached_at')
                ->nullable()
                ->after('cached_permissions')
                ->comment('Timestamp when permissions were last cached');

            $table->index('permissions_cached_at', 'idx_permissions_cached_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_permissions_cached_at');
            $table->dropColumn(['cached_permissions', 'permissions_cached_at']);
        });
    }
};
