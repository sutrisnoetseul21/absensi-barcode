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
        Schema::table('kategori_bukus', function (Blueprint $table) {
            $table->boolean('is_bisa_dipinjam')->default(true)->after('nama_kategori');
            $table->boolean('is_buku_pelajaran')->default(false)->after('is_bisa_dipinjam');
            $table->string('kode_prefix', 10)->nullable()->after('is_buku_pelajaran');
        });

        // Set default values for existing rows
        \Illuminate\Support\Facades\DB::table('kategori_bukus')->get()->each(function ($kategori) {
            $nama = strtolower(trim($kategori->nama_kategori));
            $is_bisa_dipinjam = true;
            $kode_prefix = 'SR';

            if (str_contains($nama, 'referensi')) {
                $is_bisa_dipinjam = false;
                $kode_prefix = 'RF';
            }

            \Illuminate\Support\Facades\DB::table('kategori_bukus')
                ->where('id', $kategori->id)
                ->update([
                    'is_bisa_dipinjam' => $is_bisa_dipinjam,
                    'kode_prefix' => $kode_prefix,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_bukus', function (Blueprint $table) {
            $table->dropColumn(['is_bisa_dipinjam', 'is_buku_pelajaran', 'kode_prefix']);
        });
    }
};
