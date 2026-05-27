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
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique('faculties_slug_unique');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('active')->index('faculties_status_index');
            $table->timestamps();
        });

        Schema::create('academic_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade')->index('academic_programs_faculty_id_index');
            $table->string('name');
            $table->string('slug');
            $table->string('degree_level', 50)->default('undergraduate');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('active')->index('academic_programs_status_index');
            $table->timestamps();

            $table->unique(['faculty_id', 'slug'], 'academic_programs_faculty_id_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_programs');
        Schema::dropIfExists('faculties');
    }
};
