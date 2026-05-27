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
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status', 50)->default('registered')->index('users_account_status_index');
            $table->text('account_status_reason')->nullable();
            $table->timestamp('account_restricted_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes()->index('users_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_account_status_index');
            $table->dropIndex('users_deleted_at_index');
            $table->dropColumn([
                'account_status',
                'account_status_reason',
                'account_restricted_until',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};
