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
        Schema::create('qr_timers', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 50)->index();
            $table->string('member_name', 255)->nullable();
            $table->text('qr_code');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->enum('status', ['running', 'completed'])->default('running')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_timers');
    }
};
