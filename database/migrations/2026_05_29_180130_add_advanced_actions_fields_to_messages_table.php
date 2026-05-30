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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('reply_to_message_id')->nullable()->constrained('messages')->onDelete('no action');
            $table->foreignId('forwarded_from_message_id')->nullable()->constrained('messages')->onDelete('no action');
            $table->timestamp('recalled_at')->nullable();
            $table->foreignId('recalled_by')->nullable()->constrained('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Safe drop constraints for both SQL Server and SQLite
            if (Schema::hasColumn('messages', 'reply_to_message_id')) {
                try {
                    $table->dropForeign(['reply_to_message_id']);
                } catch (Exception $e) {
                }
                $table->dropColumn('reply_to_message_id');
            }

            if (Schema::hasColumn('messages', 'forwarded_from_message_id')) {
                try {
                    $table->dropForeign(['forwarded_from_message_id']);
                } catch (Exception $e) {
                }
                $table->dropColumn('forwarded_from_message_id');
            }

            if (Schema::hasColumn('messages', 'recalled_at')) {
                $table->dropColumn('recalled_at');
            }

            if (Schema::hasColumn('messages', 'recalled_by')) {
                try {
                    $table->dropForeign(['recalled_by']);
                } catch (Exception $e) {
                }
                $table->dropColumn('recalled_by');
            }
        });
    }
};
