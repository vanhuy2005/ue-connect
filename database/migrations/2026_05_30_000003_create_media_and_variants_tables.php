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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index();
            $table->nullableMorphs('mediable'); // mediable_type (nvarchar(255)), mediable_id (bigint)
            $table->string('collection', 100)->index();
            $table->string('primary_provider', 50);
            $table->string('primary_disk', 100);
            $table->string('primary_path', 1000);
            $table->string('delivery_provider', 50)->nullable();
            $table->string('delivery_url', 1000)->nullable();
            $table->string('storage_strategy', 50);
            $table->string('visibility', 50)->default('private')->index();
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->string('extension', 20);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('checksum_sha256', 64)->index();
            $table->string('status', 50)->default('temporary')->index();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes()->index();
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->string('variant_name', 50)->index();
            $table->string('provider', 50);
            $table->string('disk', 100);
            $table->string('path', 1000);
            $table->string('url', 1000)->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->timestamps();

            $table->unique(['media_id', 'variant_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_variants');
        Schema::dropIfExists('media');
    }
};
