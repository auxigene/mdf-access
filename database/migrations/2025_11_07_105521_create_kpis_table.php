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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('kpi_name');
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('actual_value', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->enum('status', ['on_track', 'at_risk', 'off_track'])->default('on_track');
            $table->date('measured_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
