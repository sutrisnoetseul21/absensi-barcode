<?php

namespace App\Enums;

enum StatusPengajuan: string
{
    case MenungguKonfirmasi = 'Menunggu Konfirmasi';
    case Dijadwalkan = 'Dijadwalkan';
    case Selesai = 'Selesai';
    case DikonversiKeJurnal = 'Dikonversi ke Jurnal';
    case Ditolak = 'Ditolak';
}
