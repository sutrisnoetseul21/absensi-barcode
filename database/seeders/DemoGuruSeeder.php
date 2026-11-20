<?php

namespace Database\Seeders;

use App\Helpers\UsernameHelper;
use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\Kelas;
use App\Models\KelasAjaran;
use App\Models\KelompokGuruWali;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoGuruSeeder extends Seeder
{
    /**
     * Mengisi data guru acak yang realistis untuk kebutuhan tampilan video & buku panduan.
     * Aman dijalankan ulang (tidak menimpa guru eksisting berdasarkan NIP/email).
     */
    public function run(): void
    {
        $schoolSetting = PengaturanSekolah::current();
        $activeYearId = $schoolSetting?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id');

        $emailDomain = config('school.email_domain') ?: 'sekolah.sch.id';
        $roleGuru = Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);

        $daftarGuru = [
            [
                'name'          => 'Drs. Ahmad Hidayat, M.Pd.',
                'jenis_kelamin' => 'L',
                'nip'           => '197804122005011008',
                'no_hp'         => '6281227100101',
                'jabatan'       => 'Waka Kurikulum',
                'microsite_url' => 'https://sites.google.com/view/pak-hidayat-mtk',
                'wali_kelas'    => '7B',
                'mapel_codes'   => ['MTK'],
            ],
            [
                'name'          => 'Siti Rahmawati, S.Pd.',
                'jenis_kelamin' => 'P',
                'nip'           => '198507192010012014',
                'no_hp'         => '6281227100102',
                'jabatan'       => 'Waka Kesiswaan',
                'microsite_url' => 'https://sites.google.com/view/bu-rahma-bind',
                'wali_kelas'    => '8A',
                'mapel_codes'   => ['BIND'],
            ],
            [
                'name'          => 'Dewi Kusuma Wardani, S.Pd.',
                'jenis_kelamin' => 'P',
                'nip'           => '198803252014022003',
                'no_hp'         => '6281227100103',
                'jabatan'       => 'Waka Humas',
                'microsite_url' => 'https://sites.google.com/view/bu-dewi-inggris',
                'wali_kelas'    => '8B',
                'mapel_codes'   => ['BING'],
            ],
            [
                'name'          => 'Hendra Wijaya, S.Kom.',
                'jenis_kelamin' => 'L',
                'nip'           => '199011082019031006',
                'no_hp'         => '6281227100104',
                'jabatan'       => 'Kepala Lab',
                'microsite_url' => 'https://sites.google.com/view/pak-hendra-tik',
                'wali_kelas'    => '8C',
                'mapel_codes'   => ['INF'],
            ],
            [
                'name'          => 'Rina Anggraini, M.Pd.',
                'jenis_kelamin' => 'P',
                'nip'           => '198409142009032011',
                'no_hp'         => '6281227100105',
                'jabatan'       => null,
                'microsite_url' => 'https://sites.google.com/view/bu-rina-ipa',
                'wali_kelas'    => '9A',
                'mapel_codes'   => ['IPA'],
            ],
            [
                'name'          => 'Muhammad Fauzan, S.Ag.',
                'jenis_kelamin' => 'L',
                'nip'           => '198206032008011009',
                'no_hp'         => '6281227100106',
                'jabatan'       => 'Pembina Ekstrakurikuler',
                'microsite_url' => null,
                'wali_kelas'    => '9B',
                'mapel_codes'   => ['PAI'],
            ],
            [
                'name'          => 'Ratna Sari Utami, S.Pd.',
                'jenis_kelamin' => 'P',
                'nip'           => '199201172020122018',
                'no_hp'         => '6281227100107',
                'jabatan'       => null,
                'microsite_url' => 'https://sites.google.com/view/bu-ratna-ips',
                'wali_kelas'    => '9C',
                'mapel_codes'   => ['IPS'],
            ],
            [
                'name'          => 'Agus Setiawan, S.Pd.',
                'jenis_kelamin' => 'L',
                'nip'           => '198708292015021004',
                'no_hp'         => '6281227100108',
                'jabatan'       => 'Waka Sarpras',
                'microsite_url' => null,
                'wali_kelas'    => null,
                'mapel_codes'   => ['PPKN'],
            ],
            [
                'name'          => 'Nadia Puspitasari, S.Si.',
                'jenis_kelamin' => 'P',
                'nip'           => '199305112022212009',
                'no_hp'         => '6281227100109',
                'jabatan'       => null,
                'microsite_url' => 'https://sites.google.com/view/bu-nadia-sains',
                'wali_kelas'    => null,
                'mapel_codes'   => ['IPA', 'MTK'],
            ],
            [
                'name'          => 'Bagus Pratama Putra, S.Pd.',
                'jenis_kelamin' => 'L',
                'nip'           => '199412022023211005',
                'no_hp'         => '6281227100110',
                'jabatan'       => 'Pembina Ekstrakurikuler',
                'microsite_url' => null,
                'wali_kelas'    => null,
                'mapel_codes'   => ['PJOK'],
            ],
            [
                'name'          => 'Yuliana Kartika, S.Pd.',
                'jenis_kelamin' => 'P',
                'nip'           => '198910212019032012',
                'no_hp'         => '6281227100111',
                'jabatan'       => 'Pustakawan',
                'microsite_url' => 'https://sites.google.com/view/bu-yuli-seni',
                'wali_kelas'    => null,
                'mapel_codes'   => ['SBK'],
            ],
            [
                'name'          => 'Dimas Kurniawan, S.Pd.',
                'jenis_kelamin' => 'L',
                'nip'           => '199506152024211007',
                'no_hp'         => '6281227100112',
                'jabatan'       => null,
                'microsite_url' => null,
                'wali_kelas'    => null,
                'mapel_codes'   => ['IPS', 'PPKN'],
            ],
        ];

        $existingTeacherMapels = [
            '199102052020121015' => ['BIND'],
            '1234567890'         => ['BING', 'BK'],
            '1234567891'         => ['BK', 'PJOK'],
        ];

        $mapelByCode = MataPelajaran::pluck('id', 'kode_mapel')->toArray();

        $createdCount = 0;

        DB::transaction(function () use (
            $daftarGuru, $existingTeacherMapels, $mapelByCode, $activeYearId, $emailDomain, $roleGuru, &$createdCount
        ) {
            foreach ($existingTeacherMapels as $nip => $codes) {
                $existing = Guru::where('nip', $nip)->first();
                if ($existing) {
                    $ids = array_values(array_filter(array_map(fn ($code) => $mapelByCode[$code] ?? null, $codes)));
                    if (!empty($ids)) {
                        $existing->mataPelajarans()->syncWithoutDetaching($ids);
                    }
                }
            }

            foreach ($daftarGuru as $item) {
                $existingGuru = Guru::withTrashed()->where('nip', $item['nip'])->first();
                if ($existingGuru) {
                    if (!empty($item['mapel_codes'])) {
                        $ids = array_values(array_filter(array_map(fn ($code) => $mapelByCode[$code] ?? null, $item['mapel_codes'])));
                        if (!empty($ids)) {
                            $existingGuru->mataPelajarans()->syncWithoutDetaching($ids);
                        }
                    }
                    continue;
                }

                $email = UsernameHelper::generateForGuru($item['name'], $item['nip']) . '@' . $emailDomain;
                $user = User::firstWhere('email', $email);

                if (!$user) {
                    $user = User::create([
                        'name'                 => $item['name'],
                        'email'                => $email,
                        'password'             => Hash::make($item['nip']),
                        'must_change_password' => false,
                    ]);
                }
                if (!$user->hasRole($roleGuru)) {
                    $user->assignRole($roleGuru);
                }

                $guru = Guru::create([
                    'user_id'       => $user->id,
                    'name'          => $item['name'],
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'nip'           => $item['nip'],
                    'no_hp'         => $item['no_hp'],
                    'microsite_url' => $item['microsite_url'],
                ]);

                $user->update(['teacher_id' => $guru->id]);

                $guru->presensiProfile()->firstOrCreate(
                    ['teacher_id' => $guru->id],
                    [
                        'id'             => (string) Str::uuid(),
                        'barcode_code'   => $item['nip'],
                        'barcode_active' => true,
                    ]
                );

                KelompokGuruWali::firstOrCreate(
                    ['teacher_id' => $guru->id],
                    [
                        'nama_kelompok' => 'Kelompok ' . $guru->name,
                        'status_aktif'  => true,
                    ]
                );

                if (!empty($item['jabatan'])) {
                    $jabatan = Jabatan::where('nama_jabatan', $item['jabatan'])->first();
                    if ($jabatan) {
                        $guru->jabatans()->syncWithoutDetaching([
                            $jabatan->id => ['tanggal_mulai' => now('Asia/Jakarta')->toDateString()],
                        ]);
                    }
                }

                if ($activeYearId && !empty($item['wali_kelas'])) {
                    $kelas = Kelas::where('name', $item['wali_kelas'])->first();
                    if ($kelas) {
                        $alreadyAssigned = KelasAjaran::where('academic_year_id', $activeYearId)
                            ->where('class_id', $kelas->id)
                            ->exists();
                        if (!$alreadyAssigned) {
                            KelasAjaran::create([
                                'class_id'         => $kelas->id,
                                'academic_year_id' => $activeYearId,
                                'teacher_id'       => $guru->id,
                            ]);
                        }
                    }
                }

                if (!empty($item['mapel_codes'])) {
                    $ids = array_values(array_filter(array_map(fn ($code) => $mapelByCode[$code] ?? null, $item['mapel_codes'])));
                    if (!empty($ids)) {
                        $guru->mataPelajarans()->syncWithoutDetaching($ids);
                    }
                }

                $createdCount++;
            }
        });

        $this->command?->info("DemoGuruSeeder selesai: {$createdCount} guru baru ditambahkan (total guru aktif kini " . Guru::count() . ").");
    }
}

