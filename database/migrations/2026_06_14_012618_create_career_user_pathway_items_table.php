<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_user_pathway_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pathway_id')->constrained('career_user_pathways')->cascadeOnDelete();
            $table->string('item_type');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->unsignedInteger('semester_number')->nullable();
            $table->string('title')->nullable();
            $table->text('note')->nullable();
            $table->integer('order_index')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_user_pathway_items');
    }
};
