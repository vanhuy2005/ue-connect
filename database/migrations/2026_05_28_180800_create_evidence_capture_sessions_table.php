<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_capture_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('verification_request_id')->nullable()->constrained();
            $table->string('session_token_hash')->unique();
            $table->string('status');
            $table->string('required_evidence_type');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->text('client_user_agent')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('verification_request_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_capture_sessions');
    }
};
