<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_suggestions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('submitted_by');
            $table->string('suggested_name', 160);
            $table->string('community_type', 60);
            $table->text('purpose');
            $table->text('target_members');

            // Optional context fields
            $table->string('related_faculty', 160)->nullable();
            $table->unsignedBigInteger('related_program_id')->nullable();
            $table->unsignedBigInteger('proposed_owner_id')->nullable();

            // Admin review
            $table->string('status', 60)->default('submitted');
            $table->text('admin_instruction')->nullable();
            $table->text('admin_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();

            // When suggestion is converted
            $table->unsignedBigInteger('converted_community_id')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // FKs (no action to avoid SQL Server cascade paths)
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('proposed_owner_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('related_program_id')->references('id')->on('academic_programs')->onDelete('no action');
            $table->foreign('converted_community_id')->references('id')->on('communities')->onDelete('no action');

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_suggestions');
    }
};
