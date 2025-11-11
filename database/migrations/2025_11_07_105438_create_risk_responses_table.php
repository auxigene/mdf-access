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
        Schema::create('risk_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->constrained()->onDelete('cascade');
            $table->enum('response_type', ['avoid', 'mitigate', 'transfer', 'accept'])->default('mitigate');
            $table->text('action_plan')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_responses');
    }
};
