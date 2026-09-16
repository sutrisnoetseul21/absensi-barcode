<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->string('kategori', 50)->default('Umum')->after('nama_mapel');
            $table->decimal('kkm_default', 5, 2)->default(75.00)->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'kkm_default']);
        });
    }
};
