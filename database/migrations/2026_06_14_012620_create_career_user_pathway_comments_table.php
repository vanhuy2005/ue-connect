<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_user_pathway_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pathway_id')->constrained('career_user_pathways')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('parent_id')->nullable()->constrained('career_user_pathway_comments')->noActionOnDelete();
            $table->text('body');
            $table->string('status')->default('active'); // active, hidden, deleted
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_user_pathway_comments');
    }
};
