<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->string('actor_type')->default('user');
                $table->string('action');
                $table->string('target_type')->nullable();
                $table->string('target_id')->nullable()->index();
                $table->json('before_values')->nullable();
                $table->json('after_values')->nullable();
                $table->text('reason')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
