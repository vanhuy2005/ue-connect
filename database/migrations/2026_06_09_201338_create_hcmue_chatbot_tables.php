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
        // 1. Update faculties table
        Schema::table('faculties', function (Blueprint $table) {
            $table->string('code')->nullable()->index();
            $table->string('normalized_name')->nullable();
            $table->text('source_url')->nullable();
        });

        // 2. majors
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('code')->index();
            $table->string('name');
            $table->string('normalized_name')->nullable();
            $table->string('degree_level', 100)->default('undergraduate');
            $table->text('source_url')->nullable();
            $table->timestamps();
        });

        // 3. admission_cohorts
        Schema::create('admission_cohorts', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->string('cohort_name');
            $table->string('normalized_name')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 4. training_programs
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained('admission_cohorts')->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained('faculties')->noActionOnDelete();
            $table->foreignId('major_id')->constrained('majors')->noActionOnDelete();
            $table->string('title');
            $table->integer('total_credits')->default(0);
            $table->year('effective_from')->nullable();
            $table->year('effective_to')->nullable();
            $table->string('status', 50)->default('draft');
            $table->text('source_url')->nullable();
            $table->unsignedBigInteger('source_file_id')->nullable(); // Can link to media_files
            $table->string('source_hash')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 5. curriculum_course_groups
        Schema::create('curriculum_course_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('name');
            $table->string('group_type', 100)->nullable(); // e.g., general, professional, elective, required
            $table->integer('min_credits_required')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 6. curriculum_courses
        Schema::create('curriculum_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('curriculum_course_groups')->noActionOnDelete();
            $table->integer('semester')->nullable();
            $table->string('course_code')->index();
            $table->string('course_name');
            $table->string('normalized_course_name')->nullable();
            $table->integer('credits');
            $table->integer('theory_hours')->default(0);
            $table->integer('practice_hours')->default(0);
            $table->integer('self_study_hours')->default(0);
            $table->string('course_type', 100)->nullable(); // e.g., required, elective
            $table->boolean('is_required')->default(true);
            $table->text('prerequisite')->nullable(); // Text description of prerequisites
            $table->text('note')->nullable();
            $table->string('source_location')->nullable();
            $table->timestamps();
        });

        // 7. program_learning_outcomes
        Schema::create('program_learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('code')->index();
            $table->text('description');
            $table->string('category', 100)->nullable();
            $table->string('source_location')->nullable();
            $table->timestamps();
        });

        // 8. source_documents
        Schema::create('source_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 100); // e.g., student_handbook, regulation
            $table->string('title');
            $table->string('cohort', 100)->nullable();
            $table->integer('effective_year')->nullable();
            $table->text('source_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('source_hash')->nullable();
            $table->string('status', 50)->default('active');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 9. document_chunks
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_document_id')->constrained('source_documents')->cascadeOnDelete();
            $table->integer('chunk_index');
            $table->text('chunk_text');
            $table->integer('token_count')->default(0);
            $table->integer('page_start')->nullable();
            $table->integer('page_end')->nullable();
            $table->string('part')->nullable();
            $table->string('chapter')->nullable();
            $table->string('section')->nullable();
            $table->string('article')->nullable();
            $table->text('clause')->nullable();
            $table->json('metadata_json')->nullable();
            $table->string('embedding_status', 50)->default('pending'); // pending, success, failed
            $table->string('vector_id')->nullable()->index();
            $table->timestamps();
        });

        // 10. chat_sessions
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        // 11. chat_messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->string('role', 50); // user, assistant, system
            $table->text('content');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        // 12. ai_questions
        Schema::create('ai_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('original_question');
            $table->text('normalized_question')->nullable();
            $table->string('intent', 100)->nullable();
            $table->string('source_route', 100)->nullable();
            $table->double('confidence')->default(0.0);
            $table->timestamp('created_at')->useCurrent();
        });

        // 13. ai_answers
        Schema::create('ai_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('ai_questions')->cascadeOnDelete();
            $table->text('answer_text');
            $table->string('model_provider', 100)->nullable();
            $table->string('model_name', 100)->nullable();
            $table->string('prompt_version', 50)->nullable();
            $table->integer('latency_ms')->default(0);
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->string('status', 50)->default('success');
            $table->timestamp('created_at')->useCurrent();
        });

        // 14. ai_retrieved_chunks
        Schema::create('ai_retrieved_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('ai_questions')->cascadeOnDelete();
            $table->foreignId('document_chunk_id')->constrained('document_chunks')->cascadeOnDelete();
            $table->double('score');
            $table->double('rerank_score')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 15. ai_structured_queries
        Schema::create('ai_structured_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('ai_questions')->cascadeOnDelete();
            $table->string('query_type', 100);
            $table->json('filters_json')->nullable();
            $table->integer('result_count')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 16. ai_feedback
        Schema::create('ai_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('ai_answers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('rating'); // 1 = helpful, -1 = not helpful or 1-5 scale
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 17. ai_prompt_versions
        Schema::create('ai_prompt_versions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('version');
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 18. source_sync_logs
        Schema::create('source_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 100);
            $table->text('source_url')->nullable();
            $table->string('status', 50)->default('running');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->integer('records_found')->default(0);
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_sync_logs');
        Schema::dropIfExists('ai_prompt_versions');
        Schema::dropIfExists('ai_feedback');
        Schema::dropIfExists('ai_structured_queries');
        Schema::dropIfExists('ai_retrieved_chunks');
        Schema::dropIfExists('ai_answers');
        Schema::dropIfExists('ai_questions');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('source_documents');
        Schema::dropIfExists('program_learning_outcomes');
        Schema::dropIfExists('curriculum_courses');
        Schema::dropIfExists('curriculum_course_groups');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('admission_cohorts');
        Schema::dropIfExists('majors');

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn(['code', 'normalized_name', 'source_url']);
        });
    }
};
