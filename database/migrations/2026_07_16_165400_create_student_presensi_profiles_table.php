<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_presensi_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id')->unique();
            $table->string('barcode_code')->unique();
            $table->boolean('barcode_active')->default(true);
            $table->timestamps();

            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
        });

        // Copy data from students to student_presensi_profiles
        if (DB::getDriverName() === 'sqlite') {
            $students = DB::table('students')->whereNotNull('barcode_code')->get();
            foreach ($students as $student) {
                DB::table('student_presensi_profiles')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'student_id' => $student->id,
                    'barcode_code' => $student->barcode_code,
                    'barcode_active' => $student->barcode_active ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            DB::statement('
                INSERT INTO student_presensi_profiles (id, student_id, barcode_code, barcode_active, created_at, updated_at)
                SELECT UUID(), id, barcode_code, barcode_active, NOW(), NOW()
                FROM students
                WHERE barcode_code IS NOT NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_presensi_profiles');
    }
};
