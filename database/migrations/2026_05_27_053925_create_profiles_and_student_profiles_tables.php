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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique('profiles_user_id_unique')->constrained('users')->onDelete('cascade');
            $table->string('display_name');
            $table->foreignId('avatar_media_file_id')->nullable()->constrained('media_files')->onDelete('no action');
            $table->text('bio')->nullable();
            $table->string('role_type', 50)->index('profiles_role_type_index');
            $table->string('profile_status', 50)->default('incomplete')->index('profiles_profile_status_index');
            $table->string('visibility', 50)->default('public');
            $table->boolean('discoverable')->default(true)->index('profiles_discoverable_index');
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes()->index('profiles_deleted_at_index');
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique('student_profiles_profile_id_unique')->constrained('profiles')->onDelete('cascade');
            $table->string('student_code', 50)->unique('student_profiles_student_code_unique');
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('no action')->index('student_profiles_faculty_id_index');
            $table->foreignId('academic_program_id')->constrained('academic_programs')->onDelete('no action')->index('student_profiles_academic_program_id_index');
            $table->string('cohort', 50)->nullable();
            $table->integer('current_year')->nullable();
            $table->string('class_name', 100)->nullable();
            $table->text('learning_goals')->nullable();
            $table->text('career_orientation')->nullable();
            $table->timestamps();
        });

        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique('alumni_profiles_profile_id_unique')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('no action')->index('alumni_profiles_faculty_id_index');
            $table->foreignId('academic_program_id')->nullable()->constrained('academic_programs')->onDelete('no action')->index('alumni_profiles_academic_program_id_index');
            $table->string('cohort', 50)->nullable();
            $table->integer('graduation_year')->nullable();
            $table->string('current_position', 255)->nullable();
            $table->string('current_organization', 255)->nullable();
            $table->string('industry', 255)->nullable();
            $table->text('career_summary')->nullable();
            $table->boolean('willing_to_mentor')->default(false)->index('alumni_profiles_willing_to_mentor_index');
            $table->timestamps();
        });

        Schema::create('advisor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique('advisor_profiles_profile_id_unique')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('no action')->index('advisor_profiles_faculty_id_index');
            $table->string('department', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('office_location', 255)->nullable();
            $table->text('advising_areas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advisor_profiles');
        Schema::dropIfExists('alumni_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('profiles');
    }
};
