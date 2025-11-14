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
        Schema::create('fm_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code du tenant (ORANGE, IAM, ONDA, etc.)');
            $table->string('name', 100)->comment('Nom complet du tenant');
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
        Schema::dropIfExists('fm_tenants');
    }
};
