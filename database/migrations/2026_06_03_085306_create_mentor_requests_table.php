<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('mentor_profile_id')->constrained('mentor_profiles')->cascadeOnDelete();
            $table->string('topic');
            $table->text('goal');
            $table->text('question');
            $table->string('urgency')->default('normal'); // low|normal|high
            $table->text('context')->nullable();
            $table->text('expected_outcome')->nullable();
            $table->string('status')->default('submitted'); // submitted|accepted|declined|need_more_info|cancelled|completed|reported|closed
            $table->text('mentor_response')->nullable();
            $table->text('decline_reason')->nullable();
            $table->text('more_info_question')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->noActionOnDelete();

            $table->index(['student_id', 'status']);
            $table->index(['mentor_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_requests');
    }
};
