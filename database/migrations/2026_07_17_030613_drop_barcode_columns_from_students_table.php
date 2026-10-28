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
        Schema::table('students', function (Blueprint $table) {
            try {
                $table->dropUnique(['barcode_code']);
            } catch (\Throwable $e) {
                // Ignore if index doesn't exist
            }
            $table->dropColumn(['barcode_code', 'barcode_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('barcode_code')->nullable()->unique();
            $table->boolean('barcode_active')->default(true);
        });
    }
};
