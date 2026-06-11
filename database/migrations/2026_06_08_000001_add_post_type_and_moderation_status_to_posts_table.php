<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'post_type')) {
                $table->string('post_type', 60)->default('standard')->after('media_url');
            }

            if (! Schema::hasColumn('posts', 'moderation_status')) {
                $table->string('moderation_status', 60)->default('none')->after(Schema::hasColumn('posts', 'post_type') ? 'post_type' : 'media_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('posts', 'post_type')) {
                $columns[] = 'post_type';
            }

            if (Schema::hasColumn('posts', 'moderation_status')) {
                $columns[] = 'moderation_status';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
