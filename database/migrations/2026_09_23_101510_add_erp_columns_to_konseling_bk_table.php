<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konseling_bk', function (Blueprint $table) {
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->restrictOnDelete();
        });

        // Backfill data lama
        $konselings = DB::table('konseling_bk')->get();

        foreach ($konselings as $konseling) {
            $enrollment = DB::table('student_enrollments')
                ->where('student_id', $konseling->student_id)
                ->where('created_at', '<=', $konseling->tanggal_waktu)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($enrollment) {
                DB::table('konseling_bk')
                    ->where('id', $konseling->id)
                    ->update([
                        'class_id' => $enrollment->class_id,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('konseling_bk', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }
};
