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
        Schema::table('profile_privacy_settings', function (Blueprint $table) {
            $table->string('mentions_preference')->default('everyone'); // everyone, connections, nobody
            $table->string('tags_preference')->default('everyone'); // everyone, connections, nobody
            $table->string('online_status_visibility')->default('connections'); // connections, mutual_connections, nobody
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_privacy_settings', function (Blueprint $table) {
            $table->dropColumn(['mentions_preference', 'tags_preference', 'online_status_visibility']);
        });
    }
};
