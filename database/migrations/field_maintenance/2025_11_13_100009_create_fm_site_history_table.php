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
        Schema::create('fm_site_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fm_site_id')->constrained('fm_sites')->onDelete('cascade');
            $table->string('field_name', 100)->comment('Nom du champ modifié');
            $table->text('old_value')->nullable()->comment('Ancienne valeur');
            $table->text('new_value')->nullable()->comment('Nouvelle valeur');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('change_reason')->nullable()->comment('Raison du changement');
            $table->string('change_type', 50)->default('update')->comment('create, update, delete, restore');
            $table->timestamp('changed_at')->useCurrent();

            $table->index('fm_site_id');
            $table->index('field_name');
            $table->index('changed_at');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_site_history');
    }
};
