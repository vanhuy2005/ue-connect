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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('browser_push_enabled')->default(false);
            $table->boolean('email_enabled')->default(false);
            $table->boolean('greeting_notifications')->default(true);
            $table->boolean('message_notifications')->default(true);
            $table->boolean('mentor_notifications')->default(true);
            $table->boolean('community_notifications')->default(true);
            $table->boolean('safety_notifications')->default(true); // critical
            $table->boolean('moderation_notifications')->default(true); // critical
            $table->boolean('system_notifications')->default(true); // critical
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
