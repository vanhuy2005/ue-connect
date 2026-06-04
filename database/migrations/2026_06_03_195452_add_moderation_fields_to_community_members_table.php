<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_members', function (Blueprint $table) {
            // Community role within this community (owner/manager/moderator/member)
            $table->string('role', 40)->default('member')->change();

            // Optional display label (e.g. "Trưởng CLB", "Thành viên")
            $table->string('role_label', 100)->nullable()->after('role');

            // Timestamps for lifecycle events
            $table->timestamp('left_at')->nullable()->after('joined_at');
            $table->timestamp('removed_at')->nullable()->after('left_at');

            // Removal audit
            $table->unsignedBigInteger('removed_by')->nullable()->after('removed_at');
            $table->text('remove_reason')->nullable()->after('removed_by');

            $table->foreign('removed_by')->references('id')->on('users')->onDelete('no action');

            // Index for fast user-scoped queries
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('community_members', function (Blueprint $table) {
            $table->dropForeign(['removed_by']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn([
                'role_label',
                'left_at',
                'removed_at',
                'removed_by',
                'remove_reason',
            ]);
        });
    }
};
