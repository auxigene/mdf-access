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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique(); // Project Code/Number
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('project_type')->nullable(); // Construction, IT, Research, etc.
            $table->enum('methodology', ['waterfall', 'agile', 'hybrid'])->default('waterfall');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('baseline_start')->nullable();
            $table->date('baseline_end')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->enum('status', ['initiation', 'planning', 'execution', 'monitoring', 'closure', 'on_hold', 'cancelled'])->default('initiation');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('health_status', ['green', 'yellow', 'red'])->default('green');
            $table->timestamp('charter_approved_at')->nullable();
            $table->foreignId('charter_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('completion_percentage')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
