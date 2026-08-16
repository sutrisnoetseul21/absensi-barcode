<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixSiswaRoles extends Command
{
    protected $signature   = 'app:fix-siswa-roles';
    protected $description = 'Memberikan role "siswa" ke semua user yang terhubung ke data siswa tetapi belum memiliki role tersebut.';

    public function handle(): int
    {
        // Pastikan role 'siswa' ada di DB
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $this->info('Memulai pengecekan role siswa...');

        $fixed   = 0;
        $skipped = 0;
        $noUser  = 0;

        $bar = $this->output->createProgressBar(Siswa::whereNotNull('user_id')->count());
        $bar->start();

        Siswa::whereNotNull('user_id')
            ->with('user.roles')
            ->chunk(100, function ($students) use (&$fixed, &$skipped, &$noUser, $bar) {
                foreach ($students as $siswa) {
                    $bar->advance();

                    if (! $siswa->user) {
                        $noUser++;
                        continue;
                    }

                    if ($siswa->user->hasRole('siswa')) {
                        $skipped++;
                        continue;
                    }

                    $siswa->user->assignRole('siswa');
                    $fixed++;
                    $this->newLine();
                    $this->line("  ✅ <info>{$siswa->name}</info> (NISN: {$siswa->nisn}) → role siswa diberikan.");
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Role siswa diberikan (baru fix)', $fixed],
                ['Sudah punya role (di-skip)', $skipped],
                ['Tidak ada user (user_id orphan)', $noUser],
                ['Total user dengan role siswa sekarang', \App\Models\User::role('siswa')->count()],
            ]
        );

        if ($fixed > 0) {
            $this->info("Selesai! {$fixed} user siswa berhasil diperbaiki.");
        } else {
            $this->info('Semua user siswa sudah memiliki role yang benar. Tidak ada yang perlu diperbaiki.');
        }

        return self::SUCCESS;
    }
}
