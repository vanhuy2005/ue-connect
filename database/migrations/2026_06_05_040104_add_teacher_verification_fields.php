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
            $table->boolean('submitted_is_academic_advisor')->default(false)->after('submitted_organization');
            $table->text('submitted_advised_class_codes')->nullable()->after('submitted_is_academic_advisor');
        });

        Schema::table('advisor_profiles', function (Blueprint $table) {
            $table->boolean('is_academic_advisor')->default(false)->after('advising_areas');
            $table->text('advised_class_codes')->nullable()->after('is_academic_advisor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advisor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'is_academic_advisor',
                'advised_class_codes',
            ]);
        });

        Schema::table('verification_requests', function (Blueprint $table) {
            $table->dropColumn([
                'submitted_is_academic_advisor',
                'submitted_advised_class_codes',
            ]);
        });
    }
};
