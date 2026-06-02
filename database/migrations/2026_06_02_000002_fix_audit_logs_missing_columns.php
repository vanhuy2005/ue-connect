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
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        if (! Schema::hasColumn('audit_logs', 'action_key')) {
            if (Schema::hasColumn('audit_logs', 'action')) {
                Schema::table('audit_logs', function (Blueprint $table): void {
                    $table->renameColumn('action', 'action_key');
                });
            } else {
                Schema::table('audit_logs', function (Blueprint $table): void {
                    $table->string('action_key')->default('')->after('actor_type');
                });
            }
        }

        if (! Schema::hasColumn('audit_logs', 'target_type')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->string('target_type')->nullable()->after('action');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'target_id')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->string('target_id')->nullable()->after('target_type');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'before_values')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->json('before_values')->nullable()->after('target_id');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'after_values')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->json('after_values')->nullable()->after('before_values');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'reason')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->text('reason')->nullable()->after('after_values');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'ip_address')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->string('ip_address')->nullable()->after('reason');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'user_agent')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->string('user_agent')->nullable()->after('ip_address');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'metadata')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->json('metadata')->nullable()->after('user_agent');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        if (Schema::hasColumn('audit_logs', 'metadata')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('metadata');
            });
        }

        if (Schema::hasColumn('audit_logs', 'user_agent')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('user_agent');
            });
        }

        if (Schema::hasColumn('audit_logs', 'ip_address')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('ip_address');
            });
        }

        if (Schema::hasColumn('audit_logs', 'reason')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('reason');
            });
        }

        if (Schema::hasColumn('audit_logs', 'after_values')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('after_values');
            });
        }

        if (Schema::hasColumn('audit_logs', 'before_values')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('before_values');
            });
        }

        if (Schema::hasColumn('audit_logs', 'target_id')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('target_id');
            });
        }

        if (Schema::hasColumn('audit_logs', 'target_type')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('target_type');
            });
        }

        if (Schema::hasColumn('audit_logs', 'action')) {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropColumn('action');
            });
        }
    }
};
