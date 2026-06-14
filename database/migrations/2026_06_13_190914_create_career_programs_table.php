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
        Schema::create('career_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained('career_cohorts');
            $table->foreignId('faculty_id')->constrained('career_faculties');
            $table->foreignId('major_id')->constrained('career_majors');
            $table->foreignId('source_document_id')->nullable()->constrained('career_source_documents')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->index();
            $table->integer('total_credits')->nullable();
            $table->integer('total_semesters')->nullable();
            $table->string('original_dir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_programs');
    }
};
