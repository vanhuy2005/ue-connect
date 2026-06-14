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
        Schema::create('career_source_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->constrained('career_import_runs')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('document_type')->index();
            $table->string('extraction_status')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_source_documents');
    }
};
