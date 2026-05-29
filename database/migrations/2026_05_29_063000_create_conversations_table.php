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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_type', 50)->default('direct');
            $table->string('status', 50)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('direct_user_low_id')->nullable()->constrained('users')->onDelete('no action');
            $table->foreignId('direct_user_high_id')->nullable()->constrained('users')->onDelete('no action');
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Unique constraint on direct pair
            $table->unique(['conversation_type', 'direct_user_low_id', 'direct_user_high_id'], 'convo_direct_pair_unique');

            // Indexes
            $table->index('conversation_type');
            $table->index('status');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
