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
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('category')->nullable();
            $table->text('description');
            $table->integer('probability')->default(50);
            $table->integer('impact')->default(50);
            $table->integer('risk_score')->default(0);
            $table->text('mitigation_strategy')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['identified', 'assessed', 'mitigated', 'closed', 'occurred'])->default('identified');
            $table->date('identified_date');
            $table->date('review_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
