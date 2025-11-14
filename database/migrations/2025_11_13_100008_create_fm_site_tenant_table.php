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
        Schema::create('fm_site_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fm_site_id')->constrained('fm_sites')->onDelete('cascade');
            $table->foreignId('fm_tenant_id')->constrained('fm_tenants')->onDelete('cascade');
            $table->integer('tenant_rank')->default(1)->comment('Rang du tenant (1=primaire, 2=secondaire, etc.)');
            $table->boolean('is_primary')->default(false)->comment('Tenant primaire de la colocation');
            $table->text('scope_description')->nullable()->comment('Périmètre du tenant sur ce site');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Un site ne peut avoir qu'un seul tenant à un rang donné
            $table->unique(['fm_site_id', 'tenant_rank'], 'unique_site_tenant_rank');
            // Un tenant ne peut être lié qu'une fois à un site
            $table->unique(['fm_site_id', 'fm_tenant_id'], 'unique_site_tenant');

            $table->index('is_primary');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_site_tenant');
    }
};
