<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['community_id', 'user_id']);
        });

        // Add a cached members_count column to communities if missing
        if (! Schema::hasColumn('communities', 'members_count')) {
            Schema::table('communities', function (Blueprint $table) {
                $table->unsignedInteger('members_count')->default(0)->after('settings');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('communities', 'members_count')) {
            Schema::table('communities', function (Blueprint $table) {
                $table->dropColumn('members_count');
            });
        }

        Schema::dropIfExists('community_members');
    }
};
