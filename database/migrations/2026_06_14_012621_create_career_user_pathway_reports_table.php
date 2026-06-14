<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_user_pathway_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('pathway_id')->constrained('career_user_pathways')->cascadeOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, resolved, dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_user_pathway_reports');
    }
};
