<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission_key');
            $table->string('scope_type')->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();
            // use nullable unsignedBigInteger for granted_by to avoid cascade FK issues on some DB engines
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->text('reason');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('revoked_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_grants');
    }
};
