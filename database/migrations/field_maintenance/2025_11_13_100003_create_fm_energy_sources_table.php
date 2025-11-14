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
        Schema::create('fm_energy_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Code source (MT, BT, GE, etc.)');
            $table->string('name', 100)->comment('Nom complet (MOYENNE TENSION, BASSE TENSION, etc.)');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_energy_sources');
    }
};
