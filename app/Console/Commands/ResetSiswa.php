<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetSiswa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-siswa {--force : Paksa hapus tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus seluruh data siswa, profil presensi, enrollment kelas, dan akun login siswa agar siap di-import ulang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Apakah Anda YAKIN ingin menghapus SEMUA data siswa dan akun login siswa? Tindakan ini tidak dapat dibatalkan!')) {
            $this->info('Proses reset dibatalkan.');
            return 0;
        }

        $this->info('Memulai pembersihan seluruh data siswa...');

        Schema::disableForeignKeyConstraints();

        // 1. Dapatkan ID user siswa
        $studentUserIds = User::role('siswa')->pluck('id')->toArray();
        $linkedUserIds = Siswa::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray();
        $allUserIds = array_values(array_unique(array_merge($studentUserIds, $linkedUserIds)));

        // 2. Hapus relasi role Spatie dan akun User siswa
        if (!empty($allUserIds)) {
            DB::table('model_has_roles')->whereIn('model_id', $allUserIds)->delete();
            DB::table('model_has_permissions')->whereIn('model_id', $allUserIds)->delete();
            User::whereIn('id', $allUserIds)->delete();
        }

        // 3. Truncate tabel terkait siswa
        DB::table('student_enrollments')->truncate();
        DB::table('student_presensi_profiles')->truncate();
        DB::table('riwayat_pindah_kelas')->truncate();
        DB::table('attendances')->truncate();

        // 4. Force delete seluruh data Siswa
        Siswa::withTrashed()->forceDelete();

        Schema::enableForeignKeyConstraints();

        $this->info('✅ Seluruh data siswa dan akun login siswa telah BERHASIL dibersihkan 100%!');
        $this->info('Anda sekarang dapat meng-import ulang file Excel siswa.');
        return 0;
    }
}
