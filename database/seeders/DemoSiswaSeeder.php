<?php

namespace Database\Seeders;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSiswaSeeder extends Seeder
{
    /**
     * Mengisi data siswa acak agar setiap kelas memiliki minimal 3 siswa aktif terdaftar.
     * Aman dijalankan ulang (idempotent top-up per kelas, tidak menimpa siswa eksisting).
     */
    public function run(): void
    {
        $schoolSetting = PengaturanSekolah::current();
        $activeYearId = $schoolSetting?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id');

        if (!$activeYearId) {
            $this->command?->error('Tahun ajaran aktif tidak ditemukan.');
            return;
        }

        $classes = Kelas::orderBy('grade_level')->orderBy('name')->get();
        if ($classes->isEmpty()) {
            $this->command?->error('Data kelas kosong. Jalankan KelasSeeder terlebih dahulu.');
            return;
        }

        $emailDomain = config('school.email_domain') ?: 'sekolah.sch.id';
        $roleSiswa = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $kotaSekolah = $schoolSetting?->kota ?: 'Cilacap';

        $faker = fake('id_ID');
        $targetPerClass = 3;

        $namaDepanL = [
            'Ahmad', 'Bagas', 'Bima', 'Dafa', 'Dimas', 'Fajar', 'Farhan', 'Galih',
            'Hafiz', 'Ilham', 'Irfan', 'Kevin', 'Maulana', 'Muhammad', 'Naufal',
            'Pratama', 'Rafi', 'Raka', 'Rayhan', 'Reza', 'Rizky', 'Satria', 'Yoga', 'Zaki',
        ];
        $namaDepanP = [
            'Aisyah', 'Alifah', 'Amalia', 'Anisa', 'Aulia', 'Bunga', 'Citra', 'Desi',
            'Dewi', 'Dina', 'Farah', 'Fitri', 'Gita', 'Hana', 'Intan', 'Kartika',
            'Kirana', 'Lestari', 'Maya', 'Nabila', 'Nadya', 'Putri', 'Rahma', 'Salsa',
            'Siti', 'Tari', 'Tiara', 'Vina', 'Wulan', 'Zahra',
        ];
        $namaBelakang = [
            'Pratama', 'Saputra', 'Wijaya', 'Nugroho', 'Santoso', 'Hidayat', 'Kusuma',
            'Ramadhan', 'Permana', 'Kurniawan', 'Setiawan', 'Fauzan', 'Lestari',
            'Rahmawati', 'Safitri', 'Wulandari', 'Oktavia', 'Anggraini', 'Puspita',
            'Handayani', 'Sari', 'Utami', 'Mahendra', 'Hakim',
        ];
        $namaAyahDepan = [
            'Agus', 'Bambang', 'Budi', 'Dedi', 'Eko', 'Hadi', 'Hendra', 'Imam',
            'Joko', 'Mulyono', 'Slamet', 'Sudirman', 'Sugeng', 'Suharto', 'Supriyadi',
            'Surya', 'Sutrisno', 'Teguh', 'Tri', 'Wahyu', 'Widodo', 'Yusuf',
        ];
        $namaIbuDepan = [
            'Ani', 'Endang', 'Erna', 'Hartini', 'Indah', 'Lilis', 'Ningsih', 'Nurul',
            'Purwanti', 'Ratna', 'Rina', 'Sari', 'Sri', 'Sulastri', 'Sumarni',
            'Suprihatin', 'Susilowati', 'Triana', 'Wahyuni', 'Yanti', 'Yulianti',
        ];
        $pekerjaanAyah = ['Wiraswasta', 'Karyawan Swasta', 'PNS', 'Petani', 'Pedagang', 'Teknisi', 'Sopir'];
        $pekerjaanIbu = ['Ibu Rumah Tangga', 'Pedagang', 'Guru', 'Karyawan Swasta', 'Penjahit', 'Wiraswasta'];
        $sdAsal = [
            'SD Negeri 01 Majenang', 'SD Negeri 02 Majenang', 'SD Negeri 03 Majenang',
            'SD Negeri 01 Sindangsari', 'SD Negeri 02 Jenang', 'SD Muhammadiyah Majenang',
            'MI Negeri Majenang', 'SD Negeri 01 Padangjaya', 'SD Negeri 02 Cibeunying',
        ];
        $jalanList = [
            'Jl. Diponegoro', 'Jl. Ahmad Yani', 'Jl. Sudirman', 'Jl. Kartini',
            'Jl. Raya Pahonjean', 'Jl. Bhayangkara', 'Jl. Pemuda', 'Jl. Dr. Soetomo',
        ];
        $kecamatanList = ['Majenang', 'Cimanggu', 'Wanareja', 'Karangpucung', 'Cipari'];

        $createdCount = 0;
        $yearPrefix = now('Asia/Jakarta')->format('y'); // e.g. 26
        $seqCounter = 1;

        DB::transaction(function () use (
            $classes, $activeYearId, $targetPerClass, $emailDomain, $roleSiswa, $kotaSekolah,
            $faker, $namaDepanL, $namaDepanP, $namaBelakang, $namaAyahDepan, $namaIbuDepan,
            $pekerjaanAyah, $pekerjaanIbu, $sdAsal, $jalanList, $kecamatanList,
            $yearPrefix, &$seqCounter, &$createdCount
        ) {
            foreach ($classes as $kelas) {
                $existingCount = EnrollmentSiswa::where('academic_year_id', $activeYearId)
                    ->where('class_id', $kelas->id)
                    ->where('status', 'aktif')
                    ->whereHas('siswa', fn ($q) => $q->where('status', 'aktif'))
                    ->count();

                $needed = max(0, $targetPerClass - $existingCount);
                if ($needed === 0) {
                    continue;
                }

                $grade = (int) ($kelas->grade_level ?: 7);
                // Perkiraan tahun masuk & umur sesuai tingkat kelas (7, 8, 9)
                $birthYear = match ($grade) {
                    7 => 2013,
                    8 => 2012,
                    9 => 2011,
                    default => 2012,
                };
                $admissionYear = now('Asia/Jakarta')->year - max(0, $grade - 7);

                for ($i = 0; $i < $needed; $i++) {
                    // Cari NISN & NIS yang benar-benar unik (termasuk soft deleted)
                    do {
                        $nisn = '00' . $birthYear % 100 . $yearPrefix . str_pad((string) $seqCounter, 4, '0', STR_PAD_LEFT);
                        $nis = $yearPrefix . str_pad((string) $seqCounter, 4, '0', STR_PAD_LEFT);
                        $email = "{$nisn}@{$emailDomain}";
                        $seqCounter++;
                    } while (
                        Siswa::withTrashed()->where('nisn', $nisn)->exists()
                        || Siswa::withTrashed()->where('nis', $nis)->exists()
                        || User::where('email', $email)->exists()
                    );

                    $gender = ($i + $grade) % 2 === 0 ? 'L' : 'P';
                    $firstName = $gender === 'L'
                        ? $faker->randomElement($namaDepanL)
                        : $faker->randomElement($namaDepanP);
                    $lastName = $faker->randomElement($namaBelakang);
                    $fullName = "{$firstName} {$lastName}";

                    $fatherName = $faker->randomElement($namaAyahDepan) . ' ' . $faker->randomElement($namaBelakang);
                    $motherName = $faker->randomElement($namaIbuDepan) . ' ' . $faker->randomElement($namaBelakang);

                    $birthDate = Carbon::create($birthYear, rand(1, 12), rand(1, 28))->toDateString();
                    $admissionDate = Carbon::create($admissionYear, 7, rand(12, 20))->toDateString();
                    $admissionClass = '7' . substr((string) $kelas->name, -1);

                    $noHpSiswa = '628' . $faker->numerify('1########');
                    $noHpOrtu = '628' . $faker->numerify('5########');
                    $address = $faker->randomElement($jalanList)
                        . ' No. ' . rand(1, 120)
                        . ', RT 0' . rand(1, 8) . '/RW 0' . rand(1, 6)
                        . ', Kec. ' . $faker->randomElement($kecamatanList)
                        . ', Kab. ' . $kotaSekolah;

                    $user = User::create([
                        'name'                 => $fullName,
                        'email'                => $email,
                        'password'             => Hash::make($nisn),
                        'must_change_password' => false,
                    ]);
                    $user->assignRole($roleSiswa);

                    $siswa = Siswa::create([
                        'user_id'          => $user->id,
                        'nisn'             => $nisn,
                        'nis'              => $nis,
                        'name'             => $fullName,
                        'birth_place'      => $kotaSekolah,
                        'birth_date'       => $birthDate,
                        'address'          => $address,
                        'status'           => 'aktif',
                        'no_hp'            => $noHpSiswa,
                        'gender'           => $gender,
                        'religion'         => 'Islam',
                        'previous_school'  => $faker->randomElement($sdAsal),
                        'admission_date'   => $admissionDate,
                        'admission_class'  => $admissionClass,
                        'family_status'    => 'Kandung',
                        'child_order'      => rand(1, 3),
                        'nama_ayah'        => $fatherName,
                        'pekerjaan_ayah'   => $faker->randomElement($pekerjaanAyah),
                        'nama_ibu'         => $motherName,
                        'pekerjaan_ibu'    => $faker->randomElement($pekerjaanIbu),
                        'no_hp_orang_tua'  => $noHpOrtu,
                    ]);

                    EnrollmentSiswa::create([
                        'student_id'       => $siswa->id,
                        'class_id'         => $kelas->id,
                        'academic_year_id' => $activeYearId,
                        'status'           => 'aktif',
                    ]);

                    $createdCount++;
                }
            }
        });

        $this->command?->info("DemoSiswaSeeder selesai: {$createdCount} siswa baru ditambahkan (setiap kelas kini memiliki 3 siswa aktif).");
    }
}

