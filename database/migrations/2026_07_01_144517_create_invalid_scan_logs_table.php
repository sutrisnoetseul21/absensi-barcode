<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invalid_scan_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('scanned_code'); // barcode yang tidak terdaftar
            $table->dateTime('scan_time');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invalid_scan_logs');
    }
};
