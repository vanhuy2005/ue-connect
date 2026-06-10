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
            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'show_activity_status')) {
                $table->boolean('show_activity_status')->default(true);
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'client_message_id')) {
                $table->string('client_message_id', 100)->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'last_seen_at')) {
                $columns[] = 'last_seen_at';
            }
            if (Schema::hasColumn('users', 'show_activity_status')) {
                $columns[] = 'show_activity_status';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'client_message_id')) {
                $table->dropColumn('client_message_id');
            }
        });
    }
};
