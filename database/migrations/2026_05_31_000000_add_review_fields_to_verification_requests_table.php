<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_requests', function (Blueprint $table) {
            // nullable to avoid breaking existing rows in tests
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->text('review_reason')->nullable()->after('reviewed_by');
            $table->text('review_instruction')->nullable()->after('review_reason');
        });
    }

    public function down(): void
    {
        Schema::table('verification_requests', function (Blueprint $table) {
            $table->dropColumn(['reviewed_by', 'review_reason', 'review_instruction']);
        });
    }
};
