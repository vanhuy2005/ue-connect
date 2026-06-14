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
        Schema::create('career_program_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('career_programs');
            $table->foreignId('semester_id')->nullable()->constrained('career_semesters')->nullOnDelete();
            $table->foreignId('course_id')->constrained('career_courses');
            $table->foreignId('source_document_id')->nullable()->constrained('career_source_documents')->nullOnDelete();
            $table->integer('credits')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->string('knowledge_block')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_program_courses');
    }
};
