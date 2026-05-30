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
        Schema::create('conversation_user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('no action');
            $table->string('nickname', 80)->nullable();
            $table->timestamp('muted_until')->nullable();
            $table->boolean('is_restricted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            // Unique settings per conversation per user
            $table->unique(['conversation_id', 'user_id']);
            $table->index('is_restricted');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_user_settings');
    }
};
