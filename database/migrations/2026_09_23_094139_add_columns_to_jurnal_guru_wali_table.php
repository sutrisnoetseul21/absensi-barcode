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
        Schema::table('jurnal_guru_wali', function (Blueprint $table) {
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->restrictOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->restrictOnDelete();
            $table->enum('kategori_sentimen', ['Positif', 'Negatif', 'Netral'])->nullable();
        });

        // Backfill data lama
        $jurnals = DB::table('jurnal_guru_wali')->get();

        $sentimenMap = [
            '01a098cc-f7c1-7189-b7c5-2355ccb8f04e' => 'Netral',   // Trigonometri
            '01a098cd-2776-726f-a315-b2754020f844' => 'Netral',   // Jadwal ekskul robotik
            '01a0990e-5212-7116-bb45-6431b9ae8689' => 'Netral',   // Kesulitan belajar matematika
            '01a09de8-fce6-72f9-981c-242e84365a98' => 'Negatif',  // Menyendiri & cemas bicara
        ];

        foreach ($jurnals as $jurnal) {
            // Cari data enrollment aktif atau yang paling relevan dengan tanggal kejadian
            $enrollment = DB::table('student_enrollments')
                ->where('student_id', $jurnal->student_id)
                ->where('created_at', '<=', $jurnal->tanggal_waktu)
                ->orderBy('created_at', 'desc')
                ->first();

            $kategori = $sentimenMap[$jurnal->id] ?? 'Netral';

            if ($enrollment) {
                DB::table('jurnal_guru_wali')
                    ->where('id', $jurnal->id)
                    ->update([
                        'academic_year_id' => $enrollment->academic_year_id,
                        'class_id' => $enrollment->class_id,
                        'kategori_sentimen' => $kategori
                    ]);
            } else {
                // Fallback jika tidak ada enrollment (sangat jarang di sistem ERP ini)
                DB::table('jurnal_guru_wali')
                    ->where('id', $jurnal->id)
                    ->update([
                        'kategori_sentimen' => $kategori
                    ]);
            }
        }

        // Setelah backfill, kita tidak bisa dengan mudah membuat academic_year_id & class_id menjadi non-nullable 
        // dalam satu transaksi sqlite/mysql tanpa repot. Jadi biarkan nullable secara skema, namun di level aplikasi (Model/Form) kita wajibkan.
        // Hal ini aman dan umum dilakukan saat alter table dengan data legacy.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_guru_wali', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['class_id']);
            
            $table->dropColumn(['academic_year_id', 'class_id', 'kategori_sentimen']);
        });
    }
};
