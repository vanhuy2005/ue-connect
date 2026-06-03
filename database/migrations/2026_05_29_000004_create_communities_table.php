<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // avoid cascade delete on SQL Server by not using cascadeOnDelete
            $table->foreign('created_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
