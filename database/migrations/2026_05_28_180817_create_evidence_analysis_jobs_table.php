<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_analysis_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_request_id')->constrained();
            $table->foreignId('verification_evidence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_file_id')->nullable()->constrained();
            $table->string('provider');
            $table->string('model_name')->nullable();
            $table->string('status');
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('verification_request_id');
            $table->unique('verification_evidence_id');
            $table->index('status');
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_analysis_jobs');
    }
};
