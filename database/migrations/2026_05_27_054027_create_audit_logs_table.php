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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('no action');
            $table->string('actor_type', 100);
            $table->string('action_key', 100)->index('audit_logs_action_key_index');
            $table->string('target_type', 100);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('context_type', 100)->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->json('before_snapshot_json')->nullable();
            $table->json('after_snapshot_json')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index('audit_logs_created_at_index');

            $table->index(['actor_id', 'created_at'], 'audit_logs_actor_id_created_at_index');
            $table->index(['target_type', 'target_id'], 'audit_logs_target_type_target_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
