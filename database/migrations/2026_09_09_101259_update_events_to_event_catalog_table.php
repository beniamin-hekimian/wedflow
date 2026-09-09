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
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['invitation_id']);
            $table->dropColumn(['invitation_id', 'time', 'position']);

            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('sort_order');

            $table->foreignId('invitation_id')->nullable()->references('id')->on('invitations')->onDelete('cascade');
            $table->string('time')->nullable();
            $table->integer('position')->default(0);
        });
    }
};