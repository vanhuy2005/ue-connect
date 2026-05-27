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
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('verification_requests_user_id_index');
            $table->string('role_requested', 50);
            $table->string('status', 50)->default('pending_review');
            $table->string('submitted_name');
            $table->string('submitted_student_code', 50)->nullable()->index('verification_requests_submitted_student_code_index');
            $table->foreignId('submitted_faculty_id')->nullable()->constrained('faculties')->onDelete('no action');
            $table->foreignId('submitted_academic_program_id')->nullable()->constrained('academic_programs')->onDelete('no action');
            $table->string('submitted_cohort', 50)->nullable();
            $table->string('submitted_email');
            $table->text('submitted_note')->nullable();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->onDelete('no action')->index('verification_requests_assigned_admin_id_index');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes()->index('verification_requests_deleted_at_index');

            $table->index(['status', 'submitted_at'], 'verification_requests_status_submitted_at_index');
        });

        Schema::create('verification_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_request_id')->constrained('verification_requests')->onDelete('cascade')->index('verification_evidences_request_id_index');
            $table->foreignId('media_file_id')->nullable()->constrained('media_files')->onDelete('no action')->index('verification_evidences_media_file_id_index');
            $table->string('evidence_type', 50);
            $table->string('evidence_link', 1000)->nullable();
            $table->text('user_note')->nullable();
            $table->string('status', 50)->default('uploaded')->index('verification_evidences_status_index');
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_evidences');
        Schema::dropIfExists('verification_requests');
    }
};
