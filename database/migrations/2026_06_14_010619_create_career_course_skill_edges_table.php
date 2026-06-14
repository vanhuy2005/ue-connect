<?php

use App\Enums\CareerContributionSourceType;
use App\Enums\CareerSkillRelevanceLevel;
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
        Schema::create('career_course_skill_edges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('career_course_id')->constrained('career_courses')->cascadeOnDelete();
            $table->foreignId('career_skill_id')->constrained('career_skills')->cascadeOnDelete();

            // If this skill edge was created as part of a user contribution
            $table->foreignId('career_contribution_id')->nullable()->constrained('career_contributions', 'id', 'ccse_contribution_id_foreign')->cascadeOnDelete();

            $table->string('source_type')->default(CareerContributionSourceType::COMMUNITY_CONTRIBUTED->value)->index();
            $table->string('relevance_level')->default(CareerSkillRelevanceLevel::MEDIUM->value)->index();

            $table->boolean('is_active')->default(true)->index(); // Can be false if rejected

            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();

            $table->json('metadata_json')->nullable();

            $table->timestamps();

            $table->unique(['career_course_id', 'career_skill_id', 'career_contribution_id'], 'ccse_unique_edge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_course_skill_edges');
    }
};
