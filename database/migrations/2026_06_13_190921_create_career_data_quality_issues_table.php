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
        Schema::create('career_data_quality_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->constrained('career_import_runs');
            $table->foreignId('source_document_id')->nullable()->constrained('career_source_documents');
            $table->foreignId('program_id')->nullable()->constrained('career_programs');
            $table->string('issue_type')->index();
            $table->string('severity')->index();
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_data_quality_issues');
    }
};
