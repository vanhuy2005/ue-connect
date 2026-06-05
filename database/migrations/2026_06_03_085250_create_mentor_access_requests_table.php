<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('requested_role_context')->default('alumni'); // alumni | teacher | exceptional_student
            $table->string('status')->default('submitted'); // draft|submitted|under_review|approved|rejected|need_more_info|revoked
            $table->text('motivation');
            $table->text('experience_summary')->nullable();
            $table->json('expertise_topics')->nullable();
            $table->json('career_paths')->nullable();

            // New extended fields for P0 registration requirements
            $table->string('portfolio_link')->nullable();
            $table->text('availability_note')->nullable();
            $table->boolean('policy_agreed')->default(false);
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->json('help_topics')->nullable();
            $table->json('preferred_request_types')->nullable();
            $table->json('skills')->nullable();
            $table->string('response_expectation_text')->nullable();
            $table->string('office_hours_text')->nullable();

            $table->unsignedBigInteger('evidence_media_id')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('evidence_media_id')->references('id')->on('media_files')->noActionOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->noActionOnDelete();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_access_requests');
    }
};
