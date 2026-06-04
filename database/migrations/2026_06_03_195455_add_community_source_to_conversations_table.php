<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Community chat source — type = community_channel
            $table->string('source_type', 60)->nullable()->after('conversation_type');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');

            // Community display info
            $table->string('title', 200)->nullable()->after('source_id');

            // Index for community chat lookup
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id', 'title']);
        });
    }
};
