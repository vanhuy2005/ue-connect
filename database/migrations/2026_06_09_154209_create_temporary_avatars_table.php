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
        Schema::dropIfExists('temporary_avatars');
        Schema::create('temporary_avatars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->foreignId('previous_media_id')->nullable()->constrained('media')->onDelete('no action');
            $table->foreignId('current_media_id')->constrained('media')->onDelete('no action');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['expires_at', 'current_media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_avatars');
    }
};
