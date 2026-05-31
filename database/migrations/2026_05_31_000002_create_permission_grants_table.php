<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permission_grants')) {
            Schema::create('permission_grants', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('permission_key');
                $table->string('scope_type')->nullable();
                $table->unsignedBigInteger('scope_id')->nullable();
                $table->unsignedBigInteger('granted_by')->nullable()->index();
                $table->unsignedBigInteger('revoked_by')->nullable()->index();
                $table->text('reason')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('status')->default('active');
                $table->timestamp('revoked_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_grants');
    }
};
