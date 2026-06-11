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
            $table->boolean('push_messages_enabled')->default(true)->after('browser_push_enabled');
            $table->boolean('push_greetings_enabled')->default(true)->after('push_messages_enabled');
            $table->boolean('push_mentor_enabled')->default(true)->after('push_greetings_enabled');
            $table->boolean('push_community_enabled')->default(true)->after('push_mentor_enabled');
            $table->boolean('push_verification_enabled')->default(true)->after('push_community_enabled');
            $table->boolean('push_admin_announcements_enabled')->default(true)->after('push_verification_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'push_messages_enabled',
                'push_greetings_enabled',
                'push_mentor_enabled',
                'push_community_enabled',
                'push_verification_enabled',
                'push_admin_announcements_enabled',
            ]);
        });
    }
};
