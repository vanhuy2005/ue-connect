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
        Schema::create('career_position_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('career_positions')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('career_position_sections')->noActionOnDelete();
            $table->string('item_type');
            $table->nullableMorphs('target'); // target_type, target_id
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('importance_level');
            $table->string('source_type');
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
        Schema::dropIfExists('career_position_items');
    }
};
