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

            // Foreign Keys
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('template_id')->references('id')->on('templates')->onDelete('restrict');
            $table->foreignId('melody_id')->references('id')->on('melodies')->onDelete('restrict');

            // Core Info
            $table->string('slug')->unique();
            $table->string('groom_name');
            $table->string('bride_name');
            $table->string('groom_parents');
            $table->string('bride_parents');

            // Event Details
            $table->date('event_date');
            $table->time('event_time');
            $table->string('venue_name');
            $table->text('venue_address');

            // Content & Contact
            $table->text('welcome_message')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('note')->nullable();

            // Status & Payment Control
            $table->enum('status', ['draft', 'pending', 'active', 'inactive'])->default('draft');
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
