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
        Schema::create('inventaris_bukus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('buku_id')->constrained('bukus')->cascadeOnDelete();
            $table->string('no_inventaris');
            $table->date('tanggal_masuk');
            $table->enum('asal', ['Pembelian', 'Hibah', 'Tukar', 'Terbitan Sendiri']);
            $table->integer('harga')->nullable();
            $table->integer('jumlah_eksemplar')->default(0);
            $table->enum('status', ['aktif', 'dibatalkan'])->default('aktif');
            $table->text('alasan_pembatalan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_bukus');
    }
};
