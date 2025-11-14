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
        Schema::create('fm_import_logs', function (Blueprint $table) {
            $table->id();

            // Informations sur le fichier
            $table->string('file_name', 255)->comment('Nom du fichier importé');
            $table->string('file_path', 500)->nullable()->comment('Chemin du fichier');
            $table->string('file_hash', 64)->nullable()->comment('Hash MD5 du fichier pour détecter les doublons');

            // Statistiques de l'import
            $table->integer('total_rows')->default(0)->comment('Nombre total de lignes dans le fichier');
            $table->integer('successful_imports')->default(0)->comment('Imports réussis');
            $table->integer('failed_imports')->default(0)->comment('Imports échoués');
            $table->integer('warnings_count')->default(0)->comment('Nombre d\'avertissements');
            $table->integer('updated_records')->default(0)->comment('Enregistrements mis à jour');
            $table->integer('created_records')->default(0)->comment('Nouveaux enregistrements créés');

            // Détails des erreurs et warnings
            $table->json('errors')->nullable()->comment('Liste détaillée des erreurs');
            $table->json('warnings')->nullable()->comment('Liste détaillée des warnings');
            $table->json('metadata')->nullable()->comment('Métadonnées supplémentaires');

            // Statut de l'import
            $table->string('status', 20)->default('pending')->comment('pending, processing, completed, failed, cancelled');
            $table->text('failure_reason')->nullable()->comment('Raison de l\'échec si status=failed');

            // Audit
            $table->foreignId('imported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->nullable()->comment('Date de début de l\'import');
            $table->timestamp('completed_at')->nullable()->comment('Date de fin de l\'import');
            $table->integer('duration_seconds')->nullable()->comment('Durée de l\'import en secondes');
            $table->timestamps();

            $table->index('status');
            $table->index('file_hash');
            $table->index('imported_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_import_logs');
    }
};
