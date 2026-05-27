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
        Schema::create('user_identity_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index('user_identity_providers_user_id_index');
            $table->string('provider_name', 50);
            $table->string('provider_user_id');
            $table->string('provider_tenant_id')->nullable();
            $table->string('provider_email')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['provider_name', 'provider_user_id'], 'identity_providers_provider_and_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_identity_providers');
    }
};
