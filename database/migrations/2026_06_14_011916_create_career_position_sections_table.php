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
        Schema::create('career_position_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('career_positions')->cascadeOnDelete();
            $table->string('title');
            $table->string('section_type');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_position_sections');
    }
};
