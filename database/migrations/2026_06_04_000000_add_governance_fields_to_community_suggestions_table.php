<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_suggestions', function (Blueprint $table) {
            $table->string('join_policy', 40)->default('approval_required')->after('community_type');
            $table->string('visibility', 40)->default('public')->after('join_policy');
            $table->text('rules')->nullable()->after('target_members');
        });
    }

    public function down(): void
    {
        Schema::table('community_suggestions', function (Blueprint $table) {
            $table->dropColumn(['join_policy', 'visibility', 'rules']);
        });
    }
};
