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
        Schema::create('earned_value_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->date('measurement_date');
            $table->decimal('planned_value', 15, 2);
            $table->decimal('earned_value', 15, 2);
            $table->decimal('actual_cost', 15, 2);
            $table->decimal('schedule_variance', 15, 2);
            $table->decimal('cost_variance', 15, 2);
            $table->decimal('spi', 10, 4);
            $table->decimal('cpi', 10, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earned_value_metrics');
    }
};
