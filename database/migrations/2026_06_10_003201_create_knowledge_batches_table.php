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
        Schema::create('knowledge_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_key')->unique();
            $table->string('name');
            $table->string('root_path')->nullable();
            $table->string('status', 50)->default('pending'); // pending, processing, success, failed
            $table->integer('total_files')->default(0);
            $table->integer('total_imported')->default(0);
            $table->integer('total_failed')->default(0);
            $table->integer('total_needs_ocr')->default(0);
            $table->integer('total_chunks')->default(0);
            $table->integer('total_vectors')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('training_program_extraction_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_document_id')->constrained('source_documents')->cascadeOnDelete();
            $table->string('field_name', 100);
            $table->string('candidate_value', 255);
            $table->double('confidence');
            $table->text('evidence_text')->nullable();
            $table->integer('page')->nullable();
            $table->text('metadata_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('source_documents', function (Blueprint $table) {
            $table->foreignId('knowledge_batch_id')->nullable()->constrained('knowledge_batches')->nullOnDelete();
            $table->string('knowledge_batch_key', 255)->nullable()->index();
        });

        Schema::table('document_chunks', function (Blueprint $table) {
            $table->foreignId('knowledge_batch_id')->nullable()->constrained('knowledge_batches')->nullOnDelete();
            $table->string('knowledge_batch_key', 255)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->dropForeign(['knowledge_batch_id']);
            $table->dropColumn(['knowledge_batch_id', 'knowledge_batch_key']);
        });

        Schema::table('source_documents', function (Blueprint $table) {
            $table->dropForeign(['knowledge_batch_id']);
            $table->dropColumn(['knowledge_batch_id', 'knowledge_batch_key']);
        });

        Schema::dropIfExists('training_program_extraction_candidates');
        Schema::dropIfExists('knowledge_batches');
    }
};

