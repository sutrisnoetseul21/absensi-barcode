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
        Schema::table('eksemplar_bukus', function (Blueprint $table) {
            $table->foreignUuid('inventaris_buku_id')->nullable()->constrained('inventaris_bukus')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eksemplar_bukus', function (Blueprint $table) {
            $table->dropForeign(['inventaris_buku_id']);
            $table->dropColumn('inventaris_buku_id');
        });
    }
};
