<?php

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MigrateAuthToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-auth {--dry-run : Hasilkan laporan tanpa mengubah database} {--only= : Filter hanya guru atau siswa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memindahkan otentikasi Guru dan Siswa ke tabel users (Single-Auth)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info("DRY-RUN MODE AKTIF: Tidak ada data yang akan disimpan ke database.");
        }

        $domain = config('school.email_domain', 'sekolah.sch.id');

        $this->info("Memulai migrasi otentikasi...");

        // Memastikan role exist
        if (!$isDryRun) {
            Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        }

        $only = $this->option('only');

        // ==========================
        // MIGRASI GURU
        // ==========================
        if ($only === null || $only === 'guru') {
        $this->info("\n--- MEMPROSES GURU ---");
        $teachersTotal = Guru::count();
        $teachersToProcess = Guru::whereNull('user_id')->get();
        $tSkipped = $teachersTotal - $teachersToProcess->count();
        $tSuccess = 0;
        $tFailed = [];

        foreach ($teachersToProcess as $teacher) {
            try {
                if (!$isDryRun) DB::beginTransaction();

                $emailPrefix = $teacher->nip;
                if (empty($emailPrefix)) {
                    $emailPrefix = strtolower(str_replace(' ', '', $teacher->name));
                }
                
                // Cek collision email
                $email = "{$emailPrefix}@{$domain}";
                $counter = 2;
                while (User::where('email', $email)->exists()) {
                    // Pastikan user ini belum nge-link ke teacher ini
                    $existingUser = User::where('email', $email)->first();
                    if ($existingUser && Guru::where('user_id', $existingUser->id)->exists()) {
                        $email = "{$emailPrefix}{$counter}@{$domain}";
                        $counter++;
                    } else {
                        break;
                    }
                }

                $mcpStatus = ($teacher->must_change_password ?? false) ? 'True' : 'False';
                $this->line("Memproses Guru: [NIP: " . ($teacher->nip ?: 'KOSONG') . "] {$teacher->name} -> {$email} | Role: wali_kelas | MCP: {$mcpStatus}");

                if (!$isDryRun) {
                    $user = User::firstOrNew(['email' => $email]);
                    if (!$user->exists) {
                        $rawPassword = $teacher->password ?? null;
                        $user->name = $teacher->name;
                        $user->password = !empty($rawPassword) ? $rawPassword : Hash::make($teacher->nip ?: 'password');
                        $user->must_change_password = $teacher->must_change_password ?? false;
                        $user->save();

                        if (!empty($rawPassword)) {
                            DB::table('users')->where('id', $user->id)->update(['password' => $rawPassword]);
                        }
                    }

                    if (!$user->hasRole('wali_kelas')) {
                        $user->assignRole('wali_kelas');
                    }

                    $teacher->user_id = $user->id;
                    $teacher->save();

                    DB::commit();
                }
                $tSuccess++;
            } catch (\Exception $e) {
                if (!$isDryRun) DB::rollBack();
                $tFailed[] = "Guru [{$teacher->id}] {$teacher->name}: " . $e->getMessage();
                $this->error("Gagal memproses {$teacher->name}: " . $e->getMessage());
            }
        }
        }

        // ==========================
        // MIGRASI SISWA
        // ==========================
        $studentsTotal = 0; $sSuccess = 0; $sSkipped = 0; $sFailed = [];
        if ($only === null || $only === 'siswa') {
        $this->info("\n--- MEMPROSES SISWA ---");
        $studentsTotal = Siswa::count();
        $studentsToProcess = Siswa::whereNull('user_id')->get();
        $sSkipped = $studentsTotal - $studentsToProcess->count();
        $sSuccess = 0;
        $sFailed = [];

        foreach ($studentsToProcess as $student) {
            try {
                if (!$isDryRun) DB::beginTransaction();

                $emailPrefix = $student->nisn;
                if (empty($emailPrefix)) {
                    $emailPrefix = strtolower(str_replace(' ', '', $student->name));
                }

                $email = "{$emailPrefix}@{$domain}";
                $counter = 2;
                while (User::where('email', $email)->exists()) {
                    $existingUser = User::where('email', $email)->first();
                    if ($existingUser && Siswa::where('user_id', $existingUser->id)->exists()) {
                        $email = "{$emailPrefix}{$counter}@{$domain}";
                        $counter++;
                    } else {
                        break;
                    }
                }

                $mcpStatus = ($student->must_change_password ?? false) ? 'True' : 'False';
                $this->line("Memproses Siswa: [NISN: " . ($student->nisn ?: 'KOSONG') . "] {$student->name} -> {$email} | Role: siswa | MCP: {$mcpStatus}");

                if (!$isDryRun) {
                    $user = User::firstOrNew(['email' => $email]);
                    if (!$user->exists) {
                        $rawPassword = $student->password ?? null;
                        $user->name = $student->name;
                        $user->password = !empty($rawPassword) ? $rawPassword : Hash::make($student->nisn ?: 'password');
                        $user->must_change_password = $student->must_change_password ?? false;
                        $user->save();

                        if (!empty($rawPassword)) {
                            DB::table('users')->where('id', $user->id)->update(['password' => $rawPassword]);
                        }
                    }

                    if (!$user->hasRole('siswa')) {
                        $user->assignRole('siswa');
                    }

                    $student->user_id = $user->id;
                    $student->save();

                    DB::commit();
                }
                $sSuccess++;
            } catch (\Exception $e) {
                if (!$isDryRun) DB::rollBack();
                $sFailed[] = "Siswa [{$student->id}] {$student->name}: " . $e->getMessage();
                $this->error("Gagal memproses {$student->name}: " . $e->getMessage());
            }
        }
        }

        // ==========================
        // RINGKASAN
        // ==========================
        $this->info("\n=========================");
        $this->info("RINGKASAN MIGRASI");
        $this->info("=========================");
        
        if ($only === null || $only === 'guru') {
            $this->info("GURU:");
            $this->line("- Total Data : " . $teachersTotal);
            $this->line("- Berhasil   : " . $tSuccess);
            $this->line("- Di-skip    : " . $tSkipped);
            $this->line("- Gagal      : " . count($tFailed));
            if (count($tFailed) > 0) {
                foreach ($tFailed as $f) {
                    $this->error("  * " . $f);
                }
            }
        }

        if ($only === null || $only === 'siswa') {
            $this->info("\nSISWA:");
            $this->line("- Total Data : " . $studentsTotal);
            $this->line("- Berhasil   : " . $sSuccess);
            $this->line("- Di-skip    : " . $sSkipped);
            $this->line("- Gagal      : " . count($sFailed));
            if (count($sFailed) > 0) {
                foreach ($sFailed as $f) {
                    $this->error("  * " . $f);
                }
            }
        }

        $this->info("\n=========================");
        $this->info("VALIDASI OTOMATIS");
        $this->info("=========================");

        // 1. Cek format email tidak valid
        $invalidEmails = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['siswa', 'wali_kelas']);
        })->get()->filter(function($u) {
            return !filter_var($u->email, FILTER_VALIDATE_EMAIL);
        });

        if ($invalidEmails->count() > 0) {
            $this->error("❌ ADA MASALAH: " . $invalidEmails->count() . " Email tidak valid!");
            foreach ($invalidEmails as $u) {
                $this->error("   - ID: {$u->id}, Email: {$u->email}");
            }
        } else {
            $this->info("✅ LULUS: Semua format email valid.");
        }

        // 2. Cek NISN/NIP mengandung karakter non-digit
        $invalidNip = Guru::whereNotNull('user_id')->whereRaw("nip REGEXP '[^0-9]'")->get();
        $invalidNisn = Siswa::whereNotNull('user_id')->whereRaw("nisn REGEXP '[^0-9]'")->get();
        if ($invalidNip->count() > 0 || $invalidNisn->count() > 0) {
            $this->error("❌ ADA MASALAH: Ditemukan karakter non-digit pada NISN/NIP!");
            foreach ($invalidNip as $g) {
                $this->error("   - Guru NIP: {$g->nip}");
            }
            foreach ($invalidNisn as $s) {
                $this->error("   - Siswa NISN: {$s->nisn}");
            }
        } else {
            $this->info("✅ LULUS: Semua NISN/NIP bersih dari karakter non-digit.");
        }

        // 3. Cek konsistensi user_id
        $siswaUserCount = User::role('siswa')->count();
        $studentsWithUserId = Siswa::whereNotNull('user_id')->count();
        if ($siswaUserCount !== $studentsWithUserId) {
            $this->error("❌ ADA MASALAH: Ketidakcocokan jumlah user siswa ({$siswaUserCount}) dengan tabel students ({$studentsWithUserId})!");
        } else {
            $this->info("✅ LULUS: Konsistensi jumlah user_id siswa cocok ({$siswaUserCount}).");
        }

        // 4. Cek duplikat email
        $duplicateEmails = User::select('email')->groupBy('email')->havingRaw('COUNT(id) > 1')->pluck('email');
        if ($duplicateEmails->count() > 0) {
            $this->error("❌ ADA MASALAH: Ditemukan duplikat email!");
            foreach ($duplicateEmails as $email) {
                $this->error("   - Email: {$email}");
            }
        } else {
            $this->info("✅ LULUS: Tidak ada duplikat email.");
        }

        if ($isDryRun) {
            $this->info("\n(Status: DRY-RUN SELESAI, tidak ada data yang berubah di database)");
        } else {
            $this->info("\n(Status: MIGRASI SELESAI, data telah disimpan ke database)");
        }
    }
}
