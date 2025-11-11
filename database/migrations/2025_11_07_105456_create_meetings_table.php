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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('type')->nullable();
            $table->timestamp('date');
            $table->integer('duration')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('organizer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->text('action_items')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
