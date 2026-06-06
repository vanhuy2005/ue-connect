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
        Schema::create('user_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')
                ->constrained('users')
                ->onDelete('no action');
            $table->foreignId('following_id')
                ->constrained('users')
                ->onDelete('no action');
            $table->timestamps();

            $table->unique(['follower_id', 'following_id'], 'user_follows_follower_following_unique');
            $table->index('follower_id', 'user_follows_follower_id_index');
            $table->index('following_id', 'user_follows_following_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_follows');
    }
};
