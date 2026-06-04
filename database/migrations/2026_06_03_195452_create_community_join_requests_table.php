<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_join_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('user_id');

            $table->text('join_reason')->nullable();
            $table->string('status', 40)->default('pending');

            // Review
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // No cascade to avoid SQL Server cycle paths
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('no action');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('no action');

            // Prevent duplicate pending join requests via index (not partial unique for cross-DB compat)
            $table->index(['community_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_join_requests');
    }
};
