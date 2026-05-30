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
        Schema::create('conversation_pinned_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('message_id')->constrained('messages')->onDelete('no action');
            $table->foreignId('pinned_by')->constrained('users')->onDelete('no action');
            $table->timestamps();

            // Unique constraint on conversation and message to prevent duplicate pins
            $table->unique(['conversation_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_pinned_messages');
    }
};
