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
        Schema::create('verification_review_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_request_id')->constrained('verification_requests')->onDelete('cascade')->index('verification_review_actions_request_id_index');
            $table->foreignId('admin_id')->constrained('users')->onDelete('no action')->index('verification_review_actions_admin_id_index');
            $table->string('action_key', 50)->index('verification_review_actions_action_key_index');
            $table->text('reason');
            $table->text('instruction')->nullable();
            $table->json('before_snapshot_json')->nullable();
            $table->json('after_snapshot_json')->nullable();
            $table->timestamp('created_at')->nullable()->index('verification_review_actions_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_review_actions');
    }
};
