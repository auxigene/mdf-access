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
        Schema::create('fm_site_type_colocations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable()->comment('Code de la config de colocation');
            $table->string('name', 200)->comment('Nom de la configuration (ex: Inwi / Orange)');
            $table->integer('tenant_count')->default(0)->comment('Nombre de tenants dans la colocation');
            $table->json('tenants')->nullable()->comment('Liste des tenants dans la colocation');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('tenant_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_site_type_colocations');
    }
};
