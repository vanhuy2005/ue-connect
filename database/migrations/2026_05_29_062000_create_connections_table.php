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
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->onDelete('no action');
            $table->foreignId('user_two_id')->constrained('users')->onDelete('no action');
            $table->string('status', 50)->default('active');
            $table->foreignId('source_greeting_id')->nullable()->constrained('greetings')->onDelete('set null');
            $table->timestamp('connected_at')->useCurrent();
            $table->timestamp('disconnected_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Unique connections regardless of user order
            $table->unique(['user_one_id', 'user_two_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
