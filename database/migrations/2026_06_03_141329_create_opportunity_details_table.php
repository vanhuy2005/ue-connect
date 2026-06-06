<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->string('location')->nullable();
            $table->string('application_url')->nullable();
            $table->timestamp('application_deadline')->nullable();
            $table->json('field_tags')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_details');
    }
};
