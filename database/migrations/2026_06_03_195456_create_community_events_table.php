<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('created_by');

            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();

            // Event type: online/in_person/hybrid
            $table->string('event_type', 40)->default('in_person');

            // Status: draft/published/cancelled/completed
            $table->string('status', 40)->default('draft');

            // Visibility: community_members/public_preview/managers_only
            $table->string('visibility', 60)->default('community_members');

            // Scheduling
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            // Location
            $table->string('location', 500)->nullable();
            $table->string('online_link', 2000)->nullable();

            // RSVP & capacity
            $table->boolean('rsvp_required')->default(true);
            $table->timestamp('rsvp_deadline')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('waitlist_enabled')->default(false);

            // Cached RSVP counters
            $table->unsignedInteger('going_count')->default(0);
            $table->unsignedInteger('interested_count')->default(0);
            $table->unsignedInteger('waitlist_count')->default(0);

            // Cancellation
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // FKs (no cascade to avoid SQL Server cycle paths)
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('no action');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('no action');

            $table->index(['community_id', 'status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_events');
    }
};
