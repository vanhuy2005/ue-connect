<?php

use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
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
        Schema::create('career_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('target_audience')->nullable();
            $table->foreignId('related_faculty_id')->nullable()->constrained('career_faculties')->noActionOnDelete();
            $table->foreignId('related_major_id')->nullable()->constrained('career_majors')->noActionOnDelete();
            $table->foreignId('related_program_id')->nullable()->constrained('career_programs')->noActionOnDelete();
            $table->string('status')->default(CareerPositionStatus::DRAFT->value);
            $table->string('visibility')->default(CareerPositionVisibility::PUBLIC->value);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->unsignedInteger('saves_count')->default(0);
            $table->unsignedInteger('reports_count')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_positions');
    }
};
