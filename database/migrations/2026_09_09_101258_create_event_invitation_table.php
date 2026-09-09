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
        Schema::create('event_invitation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->references('id')->on('invitations')->onDelete('cascade');
            $table->foreignId('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->string('time');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['invitation_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_invitation');
    }
};