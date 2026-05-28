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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('no action');
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            // Performance indexes
            $table->index(['target_type', 'target_id', 'status']);
            $table->index(['reporter_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
