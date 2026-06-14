<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_user_pathway_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('pathway_id')->constrained('career_user_pathways')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'pathway_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_user_pathway_saves');
    }
};
