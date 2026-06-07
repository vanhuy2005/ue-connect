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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('show_activity_status')->default(true);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('client_message_id', 100)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'show_activity_status']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('client_message_id');
        });
    }
};
