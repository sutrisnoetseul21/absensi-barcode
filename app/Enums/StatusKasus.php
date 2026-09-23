<?php

namespace App\Enums;

enum StatusKasus: string
{
    case DalamPenanganan = 'Dalam Penanganan';
    case BimbinganLanjutan = 'Bimbingan Lanjutan';
    case DirujukKeAhliLuar = 'Dirujuk ke Ahli Luar';
    case TuntasSelesai = 'Tuntas / Selesai';
}
