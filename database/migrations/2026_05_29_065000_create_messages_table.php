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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('no action');
            $table->text('body')->nullable();
            $table->string('message_type', 50)->default('text');
            $table->string('status', 50)->default('sent');
            $table->foreignId('shared_post_id')->nullable()->constrained('posts')->onDelete('set null');
            $table->json('metadata_json')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
            $table->index('status');
            $table->index('shared_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
