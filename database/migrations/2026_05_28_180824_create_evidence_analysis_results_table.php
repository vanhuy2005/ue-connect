<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_job_id')->constrained('evidence_analysis_jobs')->cascadeOnDelete();
            $table->foreignId('verification_request_id')->constrained();
            $table->foreignId('verification_evidence_id')->constrained();
            $table->string('document_type_detected')->nullable();
            $table->decimal('document_type_confidence', 5, 4)->nullable();
            $table->longText('ocr_text')->nullable();
            $table->json('extracted_fields_json')->nullable();
            $table->json('match_result_json')->nullable();
            $table->json('risk_flags_json')->nullable();
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->string('recommendation');
            $table->text('review_summary')->nullable();
            $table->timestamps();

            $table->index('analysis_job_id');
            $table->index('verification_request_id');
            $table->unique('verification_evidence_id');
            $table->index('recommendation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_analysis_results');
    }
};
