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
        Schema::create('career_contribution_moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_contribution_id')->constrained('career_contributions', 'id', 'ccml_contribution_id_foreign')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users');
            $table->string('action');
            $table->text('reason')->nullable();
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_contribution_moderation_logs');
    }
};
