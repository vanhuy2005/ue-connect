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
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->boolean('push_mentions')->default(true);
            $table->boolean('push_comments')->default(true);
            $table->boolean('push_connections')->default(true);
            $table->boolean('push_messages')->default(true);
            $table->boolean('push_system')->default(true);
            $table->boolean('email_mentions')->default(true);
            $table->boolean('email_comments')->default(false);
            $table->boolean('email_connections')->default(true);
            $table->boolean('email_messages')->default(false);
            $table->boolean('email_system')->default(true);
            $table->boolean('email_marketing')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'push_mentions',
                'push_comments',
                'push_connections',
                'push_messages',
                'push_system',
                'email_mentions',
                'email_comments',
                'email_connections',
                'email_messages',
                'email_system',
                'email_marketing',
            ]);
        });
    }
};
