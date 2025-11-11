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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('version')->default('1.0');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('upload_date');
            $table->enum('status', ['draft', 'review', 'approved', 'archived'])->default('draft');
            $table->enum('access_level', ['public', 'team', 'restricted', 'confidential'])->default('team');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
