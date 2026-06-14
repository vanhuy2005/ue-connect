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
        Schema::create('career_contribution_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_contribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->tinyInteger('value')->comment('1 for upvote, -1 for downvote');
            $table->timestamps();

            // 1 user = 1 vote per contribution max
            $table->unique(['career_contribution_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_contribution_votes');
    }
};
