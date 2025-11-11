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
        Schema::create('stakeholder_engagement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stakeholder_id')->constrained()->onDelete('cascade');
            $table->enum('current_engagement', ['unaware', 'resistant', 'neutral', 'supportive', 'leading'])->default('neutral');
            $table->enum('desired_engagement', ['unaware', 'resistant', 'neutral', 'supportive', 'leading'])->default('supportive');
            $table->text('strategy')->nullable();
            $table->text('actions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakeholder_engagement');
    }
};
