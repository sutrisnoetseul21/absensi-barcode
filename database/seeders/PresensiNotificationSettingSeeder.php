<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresensiNotificationSetting;

class PresensiNotificationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'status_presensi' => 'hadir',
                'template_pesan' => "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) telah tiba di sekolah ({nama_sekolah}) pada tanggal {tanggal} pukul {jam} dengan status {status_kehadiran}.\n\nSalam,\n{nama_wali_kelas} - Wali Kelas",
                'recipients' => ['ortu'],
            ],
            [
                'status_presensi' => 'sakit',
                'template_pesan' => "Halo Orang Tua/Wali,\nKami informasikan bahwa presensi ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} tercatat {status_kehadiran}. Semoga ananda lekas sembuh.\n\nSalam,\n{nama_wali_kelas} - Wali Kelas",
                'recipients' => ['ortu', 'wali_kelas'],
            ],
            [
                'status_presensi' => 'izin',
                'template_pesan' => "Halo Orang Tua/Wali,\nKami informasikan bahwa presensi ananda {nama_siswa} (Kelas {kelas}) pada tanggal {tanggal} tercatat {status_kehadiran}.\n\nSalam,\n{nama_wali_kelas} - Wali Kelas",
                'recipients' => ['ortu', 'wali_kelas'],
            ],
            [
                'status_presensi' => 'alpa',
                'template_pesan' => "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) tidak hadir di sekolah ({nama_sekolah}) pada tanggal {tanggal} tanpa keterangan ({status_kehadiran}). Mohon konfirmasi kehadiran ananda.\n\nSalam,\n{nama_wali_kelas} - Wali Kelas\nGuru BK",
                'recipients' => [],
            ],
            [
                'status_presensi' => 'telat',
                'template_pesan' => "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) tiba di sekolah ({nama_sekolah}) pada tanggal {tanggal} pukul {jam} dengan status {status_kehadiran}.\n\nSalam,\n{nama_wali_kelas} - Wali Kelas",
                'recipients' => ['ortu', 'wali_kelas'],
            ],
            [
                'status_presensi' => 'pulang',
                'template_pesan' => "Halo Orang Tua/Wali,\nKami informasikan bahwa ananda {nama_siswa} (Kelas {kelas}) telah melakukan presensi {status_kehadiran} dari sekolah ({nama_sekolah}) pada tanggal {tanggal} pukul {jam}.\n\nSalam,\n{nama_wali_kelas} - Wali Kelas",
                'recipients' => ['ortu'],
            ]
        ];

        foreach ($statuses as $status) {
            PresensiNotificationSetting::firstOrCreate(
                ['status_presensi' => $status['status_presensi']],
                [
                    'is_active' => false,
                    'recipients' => $status['recipients'],
                    'template_pesan' => $status['template_pesan'],
                ]
            );
        }
    }
}
