<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            // Community type taxonomy
            $table->string('type', 60)->default('club')->after('slug');

            // Visibility & join policy
            $table->string('visibility', 40)->default('public')->after('description');
            $table->string('join_policy', 40)->default('approval_required')->after('visibility');

            // Short description for cards
            $table->string('short_description', 300)->nullable()->after('description');

            // Ownership & governance
            $table->unsignedBigInteger('owner_id')->nullable()->after('created_by');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('no action');

            // Media (polymorphic via Media model — stored as collection on Community morph)
            // avatar and cover are stored in the media table as mediable_type=Community
            // No FK columns needed; use morphMany collection pattern like Profile

            // Community context
            $table->string('related_faculty', 160)->nullable()->after('owner_id');
            $table->unsignedBigInteger('related_program_id')->nullable()->after('related_faculty');
            $table->foreign('related_program_id')->references('id')->on('academic_programs')->onDelete('no action');

            // Community rules
            $table->text('rules')->nullable()->after('related_program_id');

            // Cached counters (in addition to existing members_count)
            $table->unsignedInteger('post_count')->default(0)->after('rules');
            $table->unsignedInteger('resource_count')->default(0)->after('post_count');

            // Suspension details (split: internal vs user-safe)
            $table->text('suspended_reason')->nullable()->after('resource_count');
            $table->string('suspended_safe_reason', 1000)->nullable()->after('suspended_reason');
            $table->timestamp('suspended_at')->nullable()->after('suspended_safe_reason');
            $table->timestamp('archived_at')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['related_program_id']);
            $table->dropColumn([
                'type',
                'visibility',
                'join_policy',
                'short_description',
                'owner_id',
                'related_faculty',
                'related_program_id',
                'rules',
                'post_count',
                'resource_count',
                'suspended_reason',
                'suspended_safe_reason',
                'suspended_at',
                'archived_at',
            ]);
        });
    }
};
