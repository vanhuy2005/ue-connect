<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->json('expertise_topics')->nullable();
            $table->json('career_paths')->nullable();
            $table->json('skills')->nullable();
            $table->json('help_topics')->nullable();
            $table->json('preferred_request_types')->nullable();
            $table->string('availability_status')->default('available'); // available|paused|full|hidden
            $table->boolean('mentor_visibility')->default(true);
            $table->boolean('is_public_ready')->default(false); // computed trust indicator
            $table->unsignedSmallInteger('max_pending_requests')->default(5);
            $table->unsignedSmallInteger('max_monthly_accepts')->nullable();
            $table->string('response_expectation_text')->nullable();
            $table->string('office_hours_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('approved_by')->references('id')->on('users')->noActionOnDelete();

            $table->index(['availability_status', 'mentor_visibility', 'is_active', 'is_public_ready']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_profiles');
    }
};
