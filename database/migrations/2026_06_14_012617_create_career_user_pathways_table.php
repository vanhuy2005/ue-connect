<?php

use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_user_pathways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('program_id')->nullable()->constrained('career_programs')->nullOnDelete();
            $table->foreignId('career_position_id')->nullable()->constrained('career_positions')->nullOnDelete();
            $table->text('story')->nullable();
            $table->string('status')->default(CareerUserPathwayStatus::DRAFT->value);
            $table->string('visibility')->default(CareerUserPathwayVisibility::PRIVATE->value);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('saves_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('reports_count')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_user_pathways');
    }
};
