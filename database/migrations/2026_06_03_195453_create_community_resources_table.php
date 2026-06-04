<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_resources', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('community_id');
            $table->string('title', 200);
            $table->text('description')->nullable();

            // Resource type enum
            $table->string('resource_type', 60);

            // File or URL (mutually exclusive depending on type)
            $table->unsignedBigInteger('file_id')->nullable(); // FK to media_files
            $table->string('url', 2000)->nullable();

            $table->string('category', 80)->nullable();

            // Copyright attestation — required before publish
            $table->boolean('copyright_attestation')->default(false);

            // Status state machine
            $table->string('status', 60)->default('pending_review');

            // Submitter / reviewer
            $table->unsignedBigInteger('submitted_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // FKs with no action to avoid SQL Server cycle paths
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('no action');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('file_id')->references('id')->on('media_files')->onDelete('no action');

            // Performance indexes
            $table->index(['community_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_resources');
    }
};
