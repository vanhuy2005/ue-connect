<?php

use App\Enums\CareerContributionSourceType;
use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionVisibility;
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
        Schema::create('career_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Polymorphic target (can be a course, a program, etc.)
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->index(['target_type', 'target_id']);

            $table->string('contribution_type')->index();
            $table->string('title')->nullable();
            $table->text('content');

            $table->string('status')->default(CareerContributionStatus::DRAFT->value)->index();
            $table->string('visibility')->default(CareerContributionVisibility::PUBLIC->value)->index();
            $table->string('source_type')->default(CareerContributionSourceType::COMMUNITY_CONTRIBUTED->value)->index();

            $table->unsignedInteger('upvotes_count')->default(0);
            $table->unsignedInteger('downvotes_count')->default(0);
            $table->unsignedInteger('reports_count')->default(0);

            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');

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
        Schema::dropIfExists('career_contributions');
    }
};
