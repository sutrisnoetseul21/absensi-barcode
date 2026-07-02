<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom start_year & end_year dulu (nullable supaya bisa diisi dari data lama)
        if (!Schema::hasColumn('academic_years', 'start_year') || !Schema::hasColumn('academic_years', 'end_year')) {
            Schema::table('academic_years', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_years', 'start_year')) {
                    $table->integer('start_year')->nullable()->after('name');
                }
                if (!Schema::hasColumn('academic_years', 'end_year')) {
                    $table->integer('end_year')->nullable()->after('start_year');
                }
            });
        }

        // 2. Isi start_year & end_year dari data yang sudah ada (ambil dari start_date/end_date)
        if (Schema::hasColumn('academic_years', 'start_date')) {
            DB::statement("UPDATE academic_years SET start_year = YEAR(start_date), end_year = YEAR(end_date) WHERE start_date IS NOT NULL");
        }

        // 3. Jika ada baris dengan start_year masih NULL, isi dengan PHP loop
        $nullRows = DB::table('academic_years')->whereNull('start_year')->orderBy('created_at')->get();
        $baseYear = 2024;
        foreach ($nullRows as $i => $row) {
            DB::table('academic_years')->where('id', $row->id)->update([
                'start_year' => $baseYear + $i,
                'end_year'   => $baseYear + $i + 1,
            ]);
        }

        // 4. Ubah jadi NOT NULL & unique setelah terisi semua
        Schema::table('academic_years', function (Blueprint $table) {
            $table->integer('start_year')->nullable(false)->unique()->change();
            $table->integer('end_year')->nullable(false)->unique()->change();
        });

        // 5. Hapus kolom lama start_date & end_date
        Schema::table('academic_years', function (Blueprint $table) {
            if (Schema::hasColumn('academic_years', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('academic_years', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropUnique(['start_year']);
            $table->dropUnique(['end_year']);
            $table->dropColumn(['start_year', 'end_year']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }
};
