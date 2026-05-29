<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_evidences', function (Blueprint $table) {
            $table->string('capture_method')->nullable()->after('evidence_link');
            $table->timestamp('captured_at')->nullable()->after('capture_method');
            $table->foreignId('capture_session_id')
                ->nullable()
                ->after('captured_at')
                ->constrained('evidence_capture_sessions')
                ->nullOnDelete();
            $table->text('client_user_agent')->nullable()->after('capture_session_id');
            $table->decimal('image_quality_score', 5, 4)->nullable()->after('client_user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('verification_evidences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('capture_session_id');
            $table->dropColumn(['capture_method', 'captured_at', 'client_user_agent', 'image_quality_score']);
        });
    }
};
