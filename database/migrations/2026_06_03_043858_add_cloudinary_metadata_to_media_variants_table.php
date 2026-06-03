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
        Schema::table('media_variants', function (Blueprint $table) {
            $table->string('cloudinary_public_id', 1000)->nullable()->after('url');
            $table->unsignedBigInteger('cloudinary_version')->nullable()->after('cloudinary_public_id');
            $table->string('cloudinary_secure_url', 1000)->nullable()->after('cloudinary_version');
            $table->string('cloudinary_format', 50)->nullable()->after('cloudinary_secure_url');
            $table->unsignedBigInteger('cloudinary_bytes')->nullable()->after('cloudinary_format');
            $table->string('cloudinary_resource_type', 50)->nullable()->after('cloudinary_bytes');
            $table->timestamp('cloudinary_synced_at')->nullable()->after('cloudinary_resource_type');
            $table->string('cloudinary_sync_status', 50)->default('skipped')->index()->after('cloudinary_synced_at');
            $table->string('cloudinary_error_code', 100)->nullable()->after('cloudinary_sync_status');
            $table->string('cloudinary_error_message', 500)->nullable()->after('cloudinary_error_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_variants', function (Blueprint $table) {
            $table->dropColumn([
                'cloudinary_public_id',
                'cloudinary_version',
                'cloudinary_secure_url',
                'cloudinary_format',
                'cloudinary_bytes',
                'cloudinary_resource_type',
                'cloudinary_synced_at',
                'cloudinary_sync_status',
                'cloudinary_error_code',
                'cloudinary_error_message',
            ]);
        });
    }
};
