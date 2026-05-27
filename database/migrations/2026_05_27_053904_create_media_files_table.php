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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade')->index('media_files_owner_id_index');
            $table->string('disk', 100);
            $table->string('path', 1000);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->string('extension', 20);
            $table->unsignedBigInteger('size_bytes');
            $table->string('visibility', 50)->default('private')->index('media_files_visibility_index');
            $table->string('file_category', 50)->index('media_files_file_category_index');
            $table->string('checksum', 128)->nullable()->index('media_files_checksum_index');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes()->index('media_files_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
