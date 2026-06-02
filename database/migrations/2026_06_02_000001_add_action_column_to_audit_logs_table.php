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
        if (Schema::hasTable('audit_logs') && ! Schema::hasColumn('audit_logs', 'action_key')) {
            if (Schema::hasColumn('audit_logs', 'action')) {
                Schema::table('audit_logs', function (Blueprint $table): void {
                    $table->renameColumn('action', 'action_key');
                });
            } else {
                Schema::table('audit_logs', function (Blueprint $table): void {
                    $table->string('action_key')->after('actor_type');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'action')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('action');
            });
        }
    }
};
