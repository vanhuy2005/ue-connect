<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentor_access_requests', function (Blueprint $table) {
            $table->dropForeign(['evidence_media_id']);
            $table->foreign('evidence_media_id')->references('id')->on('media')->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mentor_access_requests', function (Blueprint $table) {
            $table->dropForeign(['evidence_media_id']);
            $table->foreign('evidence_media_id')->references('id')->on('media_files')->noActionOnDelete();
        });
    }
};
