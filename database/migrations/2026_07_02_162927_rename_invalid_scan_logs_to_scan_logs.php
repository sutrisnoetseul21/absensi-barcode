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
        Schema::dropIfExists('invalid_scan_logs');

        Schema::create('scan_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('barcode_code')->nullable();
            $table->foreignUuid('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('status');
            $table->dateTime('scan_time');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_logs');

        Schema::create('invalid_scan_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('scanned_code');
            $table->dateTime('scan_time');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }
};
