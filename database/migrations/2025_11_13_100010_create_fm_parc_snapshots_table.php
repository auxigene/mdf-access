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
        Schema::create('fm_parc_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique()->comment('Date du snapshot');
            $table->string('version', 50)->nullable()->comment('Version du parc (ex: V. Avril 2025)');
            $table->text('description')->nullable();

            // Statistiques du snapshot
            $table->integer('total_sites')->default(0);
            $table->integer('active_sites')->default(0);
            $table->integer('inactive_sites')->default(0);
            $table->integer('colocation_sites')->default(0);

            // Snapshot complet du parc (peut être volumineux)
            $table->json('data_snapshot')->nullable()->comment('Export JSON complet du parc à cette date');
            $table->json('statistics')->nullable()->comment('Statistiques détaillées');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();

            $table->index('snapshot_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_parc_snapshots');
    }
};
