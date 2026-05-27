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
        Schema::table('verification_requests', function (Blueprint $table) {
            $table->string('requested_identity_type', 50)->nullable()->after('role_requested');
            $table->string('submitted_graduation_year', 10)->nullable()->after('submitted_cohort');
            $table->string('submitted_old_student_email')->nullable()->after('submitted_email');
            $table->string('submitted_position')->nullable()->after('submitted_note');
            $table->string('submitted_organization')->nullable()->after('submitted_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verification_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requested_identity_type',
                'submitted_graduation_year',
                'submitted_old_student_email',
                'submitted_position',
                'submitted_organization',
            ]);
        });
    }
};
