<?php

namespace App\Console\Commands;

use App\Models\Guru;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixGuruRoles extends Command
{
    protected $signature   = 'app:fix-guru-roles';
    protected $description = 'Memberikan role "wali_kelas" ke semua user yang terhubung ke data guru tetapi belum memiliki role tersebut.';

    public function handle(): int
    {
        // Pastikan role 'wali_kelas' ada di DB
        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);

        $this->info('Memulai pengecekan role guru...');

        $fixed   = 0;
        $skipped = 0;
        $noUser  = 0;

        $bar = $this->output->createProgressBar(Guru::whereNotNull('user_id')->count());
        $bar->start();

        Guru::whereNotNull('user_id')
            ->with('user.roles')
            ->chunk(100, function ($teachers) use (&$fixed, &$skipped, &$noUser, $bar) {
                foreach ($teachers as $guru) {
                    $bar->advance();

                    if (! $guru->user) {
                        $noUser++;
                        continue;
                    }

                    if ($guru->user->hasRole('wali_kelas')) {
                        $skipped++;
                        continue;
                    }

                    $guru->user->assignRole('wali_kelas');
                    $fixed++;
                    $this->newLine();
                    $this->line("  ✅ <info>{$guru->name}</info> → role wali_kelas diberikan.");
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Role wali_kelas diberikan (baru fix)', $fixed],
                ['Sudah punya role (di-skip)', $skipped],
                ['Tidak ada user (user_id orphan)', $noUser],
                ['Total user dengan role wali_kelas sekarang', \App\Models\User::role('wali_kelas')->count()],
            ]
        );

        if ($fixed > 0) {
            $this->info("Selesai! {$fixed} user guru berhasil diperbaiki.");
        } else {
            $this->info('Semua user guru sudah memiliki role yang benar. Tidak ada yang perlu diperbaiki.');
        }

        return self::SUCCESS;
    }
}
