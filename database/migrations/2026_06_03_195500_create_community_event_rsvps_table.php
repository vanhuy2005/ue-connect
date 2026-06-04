<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_event_rsvps', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');

            // Status: going/interested/declined/cancelled/waitlisted
            $table->string('status', 40)->default('going');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('community_events')->onDelete('no action');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');

            $table->unique(['event_id', 'user_id']);
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_event_rsvps');
    }
};
