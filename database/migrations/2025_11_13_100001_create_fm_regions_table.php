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
        Schema::create('fm_regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code court de la région (ex: Marrakech, Casa)');
            $table->string('name', 100)->comment('Nom complet de la ZI');
            $table->string('zone_geographique', 50)->nullable()->comment('Zone géographique (SUD, NORD, etc.)');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_region_id')->nullable()->comment('Pour hiérarchie de régions');
            $table->integer('level')->default(1)->comment('Niveau hiérarchique');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_region_id')->references('id')->on('fm_regions')->onDelete('set null');
            $table->index('status');
            $table->index('zone_geographique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_regions');
    }
};
