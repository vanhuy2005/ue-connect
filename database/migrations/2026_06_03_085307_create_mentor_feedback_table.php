<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_request_id')->constrained('mentor_requests')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->noActionOnDelete();
            $table->string('helpfulness_level'); // helpful|somewhat_helpful|not_helpful
            $table->text('feedback_text')->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamps();

            $table->unique('mentor_request_id'); // one feedback per request
            $table->index('mentor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_feedback');
    }
};
