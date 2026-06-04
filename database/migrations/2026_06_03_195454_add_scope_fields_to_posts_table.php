<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Community feed scoping — null means home feed (backward compatible)
            $table->string('scope_type', 60)->nullable()->after('user_id');
            $table->unsignedBigInteger('scope_id')->nullable()->after('scope_type');

            // Community post type (standard_post/announcement/resource_share/question/discussion/system_update)
            $table->string('community_post_type', 60)->nullable()->after('scope_id');

            // Pinning support for community feeds
            $table->timestamp('pinned_at')->nullable()->after('community_post_type');
            $table->unsignedBigInteger('pinned_by')->nullable()->after('pinned_at');

            // Performance index for community feed queries
            $table->index(['scope_type', 'scope_id', 'status', 'created_at']);
            $table->foreign('pinned_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['pinned_by']);
            $table->dropIndex(['scope_type', 'scope_id', 'status', 'created_at']);
            $table->dropColumn(['scope_type', 'scope_id', 'community_post_type', 'pinned_at', 'pinned_by']);
        });
    }
};
