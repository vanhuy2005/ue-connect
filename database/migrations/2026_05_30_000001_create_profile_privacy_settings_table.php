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
        Schema::create('profile_privacy_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('profile_visibility')->default('public_to_verified'); // public_to_verified, connections_only, private, hidden_by_moderation
            $table->string('discovery_visibility')->default('enabled'); // enabled, disabled, paused, forced_hidden
            $table->boolean('show_faculty')->default(true);
            $table->boolean('show_major')->default(true);
            $table->boolean('show_cohort')->default(true);
            $table->boolean('show_class_code')->default(false);
            $table->boolean('show_bio')->default(true);
            $table->boolean('show_interests')->default(true);
            $table->boolean('show_connection_goals')->default(true);
            $table->boolean('show_communities')->default(false);
            $table->boolean('show_career_info')->default(false);
            $table->boolean('show_mentor_topics')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_privacy_settings');
    }
};
