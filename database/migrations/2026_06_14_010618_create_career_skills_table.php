<?php

use App\Enums\CareerSkillCategory;
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
        Schema::create('career_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('normalized_name')->unique();
            $table->string('category')->default(CareerSkillCategory::TECHNICAL->value)->index();
            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->boolean('is_active')->default(true)->index(); // Can be used to disable generic/spam skills
            $table->json('metadata_json')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_skills');
    }
};
