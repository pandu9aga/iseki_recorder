<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_parent_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik', 50);
            $table->string('member_name');
            $table->timestamps();
        });

        Schema::create('rack_qr_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_parent_folder_id')->constrained('rack_parent_folders')->onDelete('cascade');
            $table->string('qr_code');
            $table->timestamps();
        });

        Schema::create('rack_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_qr_folder_id')->constrained('rack_qr_folders')->onDelete('cascade');
            $table->string('photo_path');
            $table->string('filename');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_photos');
        Schema::dropIfExists('rack_qr_folders');
        Schema::dropIfExists('rack_parent_folders');
    }
};
