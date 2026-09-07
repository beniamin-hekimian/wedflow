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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('template_id')->references('id')->on('templates')->onDelete('restrict');
            $table->foreignId('melody_id')->references('id')->on('melodies')->onDelete('restrict');

            $table->string('slug')->unique();
            $table->string('groom_name');
            $table->string('bride_name');

            $table->date('event_date');
            $table->time('event_time');
            $table->string('venue_name');
            $table->text('venue_address');

            $table->string('contact_phone')->nullable();
            $table->text('note')->nullable();

            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
