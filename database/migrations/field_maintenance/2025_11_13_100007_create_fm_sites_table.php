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
        Schema::create('fm_sites', function (Blueprint $table) {
            $table->id();

            // Identification du site
            $table->string('site_code', 50)->unique()->comment('Code unique du site (SiteID)');
            $table->string('gsm_id', 50)->nullable()->comment('GSM ID du site');
            $table->string('site_name', 200)->nullable()->comment('Nom du site');

            // Relations vers tables de référence
            $table->foreignId('fm_region_id')->nullable()->constrained('fm_regions')->onDelete('set null');
            $table->foreignId('fm_site_class_id')->nullable()->constrained('fm_site_classes')->onDelete('set null');
            $table->foreignId('fm_energy_source_id')->nullable()->constrained('fm_energy_sources')->onDelete('set null');
            $table->foreignId('fm_maintenance_typology_id')->nullable()->constrained('fm_maintenance_typologies')->onDelete('set null');
            $table->foreignId('fm_site_type_colocation_id')->nullable()->constrained('fm_site_type_colocations')->onDelete('set null');

            // Colocation
            $table->boolean('is_colocation')->default(false)->comment('Le site est-il en colocation');
            $table->json('colocation_details')->nullable()->comment('Détails de la colocation (tenants, etc.)');

            // Localisation
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('address')->nullable();

            // Métadonnées
            $table->json('technical_specs')->nullable()->comment('Spécifications techniques');
            $table->json('metadata')->nullable()->comment('Données supplémentaires de l\'Excel');

            // Gestion du statut
            $table->string('status', 20)->default('active')->comment('active, inactive, decommissioned');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();

            // Audit
            $table->timestamps();
            $table->softDeletes();

            // Index pour performances
            $table->index('site_code');
            $table->index('gsm_id');
            $table->index('status');
            $table->index('is_colocation');
            $table->index('fm_region_id');
            $table->index('fm_site_class_id');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_sites');
    }
};
