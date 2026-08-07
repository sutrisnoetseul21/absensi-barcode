<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bukus', function (Blueprint $table) {
            // Menyimpan biblio_id asli dari SLiMS agar eksemplar bisa di-lookup
            // langsung dari tabel bukus, tanpa bergantung pada Cache Laravel.
            $table->unsignedInteger('slims_biblio_id')->nullable()->index()->after('isbn');
        });
    }

    public function down(): void
    {
        Schema::table('bukus', function (Blueprint $table) {
            $table->dropIndex(['slims_biblio_id']);
            $table->dropColumn('slims_biblio_id');
        });
    }
};
