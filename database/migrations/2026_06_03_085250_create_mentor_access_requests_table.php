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
            $table->string('requested_role_context')->default('alumni'); // alumni | advisor | exceptional_student
            $table->string('status')->default('submitted'); // draft|submitted|under_review|approved|rejected|need_more_info|revoked
            $table->text('motivation');
            $table->text('experience_summary')->nullable();
            $table->json('expertise_topics')->nullable();
            $table->json('career_paths')->nullable();
            $table->unsignedBigInteger('evidence_media_id')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_access_requests');
    }
};
