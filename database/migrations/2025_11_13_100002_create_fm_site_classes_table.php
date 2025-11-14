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
        Schema::create('fm_site_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Code de la classe (A, B, C, D, E)');
            $table->string('name', 100)->comment('Nom de la classe');
            $table->text('description')->nullable();
            $table->integer('priority')->default(0)->comment('Priorité pour le tri');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_site_classes');
    }
};
