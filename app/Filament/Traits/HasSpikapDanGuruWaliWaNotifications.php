<?php

namespace App\Filament\Traits;

use App\Models\Jabatan;
use App\Models\PresensiNotificationSetting;
use App\Models\SpikapNotifSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\HtmlString;

trait HasSpikapDanGuruWaliWaNotifications
{
    protected function loadSpikapDanGuruWaliData(array &$data): void
    {
        $spikapSetting = SpikapNotifSetting::instance();

        $spikapDarurat = PresensiNotificationSetting::where('status_presensi', 'spikap_darurat')->first();
        if ($spikapDarurat) {
            $data['spikap_darurat_is_active']      = $spikapDarurat->is_active;
            $data['spikap_darurat_recipients']     = $spikapDarurat->recipients ?? $spikapSetting->getActiveEmergencyHandlers();
            $data['spikap_darurat_template_pesan'] = $spikapDarurat->template_pesan;
        } else {
            $data['spikap_darurat_is_active']      = true;
            $data['spikap_darurat_recipients']     = $spikapSetting->getActiveEmergencyHandlers() ?: ['wali_kelas', 'kepala_sekolah'];
            $data['spikap_darurat_template_pesan'] = "🚨 *LAPORAN DARURAT {nama_aplikasi}*\nDari: {nama_siswa}\nKelas: {nama_kelas}\nJenis: {jenis}\nWaktu Lapor: {waktu}\n\nSilakan buka Portal Guru untuk detail dan tindak lanjut segera.";
        }

        $spikapBiasa = PresensiNotificationSetting::where('status_presensi', 'spikap_biasa')->first();
        if ($spikapBiasa) {
            $data['spikap_biasa_is_active']      = $spikapBiasa->is_active;
            $data['spikap_biasa_template_pesan'] = $spikapBiasa->template_pesan;
        } else {
            $data['spikap_biasa_is_active']      = $spikapSetting->shouldNotifyGuruLaporanBiasa();
            $data['spikap_biasa_template_pesan'] = "📋 *LAPORAN {nama_aplikasi} BARU*\nTujuan: {tujuan_guru}\nDari: {nama_siswa}\nKelas: {nama_kelas}\nJenis: {jenis}\nWaktu Lapor: {waktu}\n\nSilakan buka Portal Guru untuk meninjau detail aduan dan melakukan tindak lanjut.";
        }

        $spikapApresiasiSiswa = PresensiNotificationSetting::where('status_presensi', 'spikap_apresiasi_siswa')->first();
        $spikapApresiasiOrtu  = PresensiNotificationSetting::where('status_presensi', 'spikap_apresiasi_ortu')->first();
        $data['spikap_apresiasi_is_active']      = $spikapSetting->shouldNotifySiswaApresiasi();
        $data['spikap_apresiasi_targets']        = $spikapSetting->getTargetPenerimaSiswa() ?? ['siswa'];
        $data['spikap_apresiasi_siswa_template'] = $spikapApresiasiSiswa?->template_pesan ?? "Halo *{nama_siswa}*,\n\nTerima kasih atas keberanianmu telah melapor di {nama_aplikasi}. Laporanmu (*{jenis}*) telah berhasil kami terima dengan aman dan rahasia.\n\nPihak sekolah ({tujuan_guru}) akan segera meninjau dan menindaklanjuti laporan ini. Tetap tenang dan selalu jaga keselamatan dirimu. Kami ada bersamamu.\n\n_Pesan otomatis Layanan {nama_aplikasi} Sekolah_";
        $data['spikap_apresiasi_ortu_template']  = $spikapApresiasiOrtu?->template_pesan ?? "Yth. Orang Tua/Wali dari *{nama_siswa}*,\n\nPemberitahuan bahwa ananda telah menyampaikan aduan melalui layanan {nama_aplikasi} sekolah (*{jenis}*). Laporan telah tercatat dengan aman dan sedang dalam penanganan pihak sekolah ({tujuan_guru}).\n\nSekolah berkomitmen menjaga keselamatan dan kenyamanan setiap siswa.\n\n_Layanan Bimbingan & Konseling {nama_aplikasi} Sekolah_";

        $spikapStatusSiswa = PresensiNotificationSetting::where('status_presensi', 'spikap_status_siswa')->first();
        $spikapStatusOrtu  = PresensiNotificationSetting::where('status_presensi', 'spikap_status_ortu')->first();
        $data['spikap_status_is_active']      = $spikapSetting->shouldNotifySiswaTindakLanjut();
        $data['spikap_status_siswa_template'] = $spikapStatusSiswa?->template_pesan ?? "Halo *{nama_siswa}*,\n\nAda perkembangan terbaru mengenai laporan {nama_aplikasi} yang kamu kirimkan:\n\n📌 *Status:* {status_baru}{catatan}\n\nKamu juga dapat melihat riwayat lengkap perkembangan kasusmu di Portal Siswa.\n\n_Layanan {nama_aplikasi} Sekolah_";
        $data['spikap_status_ortu_template']  = $spikapStatusOrtu?->template_pesan ?? "Yth. Orang Tua/Wali dari *{nama_siswa}*,\n\nKami menginformasikan perkembangan penanganan atas laporan {nama_aplikasi} ananda *{nama_siswa}*:\n\n📌 *Status:* {status_baru}{catatan}\n\nTerima kasih atas perhatian dan kerja samanya dalam mendampingi ananda.\n\n_Layanan Bimbingan & Konseling {nama_aplikasi} Sekolah_";

        $gwPengajuan = PresensiNotificationSetting::where('status_presensi', 'guru_wali_pengajuan')->first();
        if ($gwPengajuan) {
            $data['guru_wali_pengajuan_is_active']      = $gwPengajuan->is_active;
            $data['guru_wali_pengajuan_template_pesan'] = $gwPengajuan->template_pesan;
        } else {
            $data['guru_wali_pengajuan_is_active']      = true;
            $data['guru_wali_pengajuan_template_pesan'] = "Halo Bapak/Ibu *{nama_guru_wali}*,\n\nSiswa bimbingan Anda, *{nama_siswa}* (Kelas {kelas}), baru saja menyampaikan cerita / permohonan bimbingan melalui Ruang Aman SI-WALI:\n\n📌 *Kategori:* {kategori}\n📌 *Mode:* {mode}\n📌 *Topik:* {topik}\n📝 *Pesan Siswa:* \"{detail}\"\n\nSilakan buka Portal Guru Wali pada aplikasi untuk membaca detail atau merespons permohonan bimbingan ananda. Terima kasih.";
        }

        $gwBalasan = PresensiNotificationSetting::where('status_presensi', 'guru_wali_balasan')->first();
        $gwBalasanOrtu = PresensiNotificationSetting::where('status_presensi', 'guru_wali_balasan_ortu')->first();
        if ($gwBalasan) {
            $data['guru_wali_balasan_is_active']      = $gwBalasan->is_active;
            $data['guru_wali_balasan_targets']        = $gwBalasan->recipients ?? ['siswa'];
            $data['guru_wali_balasan_siswa_template'] = $gwBalasan->template_pesan;
        } else {
            $data['guru_wali_balasan_is_active']      = true;
            $data['guru_wali_balasan_targets']        = ['siswa'];
            $data['guru_wali_balasan_siswa_template'] = "Halo *{nama_siswa}*,\n\nBapak/Ibu Guru Wali (*{nama_guru_wali}*) telah merespons cerita / konsultasi kamu mengenai *{topik}*:\n\n💬 *Pesan Guru Wali:*\n\"{balasan_guru}\"\n\nBuka aplikasi presensi mobile (menu SI-WALI) untuk melihat dan melanjutkan obrolan.";
        }
        $data['guru_wali_balasan_ortu_template'] = $gwBalasanOrtu?->template_pesan ?? "Yth. Orang Tua/Wali dari *{nama_siswa}*,\n\nKami informasikan bahwa Guru Wali ananda (*{nama_guru_wali}*) telah memberikan tanggapan terkait pendampingan/bimbingan ananda mengenai topik *{topik}*.\n\nTerima kasih atas kerja samanya dalam mendukung perkembangan ananda.";

        $gwJadwal = PresensiNotificationSetting::where('status_presensi', 'guru_wali_jadwal')->first();
        if ($gwJadwal) {
            $data['guru_wali_jadwal_is_active'] = $gwJadwal->is_active;
            $data['guru_wali_jadwal_targets']   = $gwJadwal->recipients ?? ['siswa'];
            $data['guru_wali_jadwal_template']  = $gwJadwal->template_pesan;
        } else {
            $data['guru_wali_jadwal_is_active'] = true;
            $data['guru_wali_jadwal_targets']   = ['siswa'];
            $data['guru_wali_jadwal_template']  = "Halo *{nama_siswa}*,\n\nJadwal pertemuan tatap muka bimbingan dengan Guru Wali (*{nama_guru_wali}*) untuk topik *{topik}* telah ditetapkan:\n\n🗓️ *Waktu Pertemuan:* {jadwal_pasti}\n📝 *Catatan:* {catatan_guru}\n\nMohon hadir tepat waktu ya. Tetap semangat!";
        }
    }

    protected function getSpikapDaruratOptions(): array
    {
        $fixed = [
            'kepala_sekolah' => 'Kepala Sekolah',
            'wali_kelas'     => 'Wali Kelas',
            'guru_bk'        => 'Guru BK',
            'waka_kesiswaan' => 'Waka Kesiswaan',
        ];
        try {
            $excludeNames = [
                'Kepala Sekolah', 'Guru BK', 'BK', 'Bimbingan Konseling', 'Bimbingan dan Konseling',
                'Waka Kesiswaan', 'Wakil Kepala Sekolah Bidang Kesiswaan',
                'Wakil Kepala Sekolah Bagian Kesiswaan', 'Kesiswaan',
            ];
            $dynamic = Jabatan::whereNotIn('nama_jabatan', $excludeNames)
                ->orderBy('nama_jabatan')
                ->pluck('nama_jabatan', 'nama_jabatan')
                ->mapWithKeys(fn ($name, $val) => ['jabatan:' . $val => $name])
                ->toArray();
            return array_merge($fixed, $dynamic);
        } catch (\Throwable $e) {
            return $fixed;
        }
    }

    protected function getSpikapWaTab(): Tab
    {
        $namaApp = SpikapNotifSetting::instance()->getNamaAplikasi();
        return Tab::make('Notifikasi SPIKAP')
            ->icon('heroicon-o-shield-exclamation')
            ->schema([
                Section::make('Aturan Notifikasi: Laporan Darurat')
                    ->description("Sistem otomatis mengirimkan pesan WA prioritas tinggi ke petugas/pejabat sekolah begitu laporan darurat {$namaApp} masuk.")
                    ->schema([
                        Toggle::make('spikap_darurat_is_active')
                            ->label('Aktifkan Notifikasi WA Laporan Darurat')
                            ->columnSpanFull(),
                        Select::make('spikap_darurat_recipients')
                            ->label('Penerima Notifikasi Darurat')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih jabatan penerima darurat...')
                            ->options(fn () => $this->getSpikapDaruratOptions())
                            ->helperText('Petugas yang dipilih akan menerima pesan WhatsApp seketika dan berhak menindaklanjuti kasus darurat.')
                            ->columnSpanFull(),
                        Textarea::make('spikap_darurat_template_pesan')
                            ->label('Template Pesan WA Laporan Darurat')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_aplikasi}, {nama_siswa}, {nama_kelas}, {jenis}, {waktu}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Aturan Notifikasi: Laporan Biasa (Ke Guru)')
                    ->description('Sistem otomatis mengirimkan pesan WA ke Wali Kelas atau Guru BK yang dituju saat siswa mengirimkan aduan biasa.')
                    ->schema([
                        Toggle::make('spikap_biasa_is_active')
                            ->label('Aktifkan Notifikasi WA Laporan Biasa ke Guru')
                            ->columnSpanFull(),
                        Textarea::make('spikap_biasa_template_pesan')
                            ->label('Template Pesan WA Laporan Biasa')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_aplikasi}, {tujuan_guru}, {nama_siswa}, {nama_kelas}, {jenis}, {waktu}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Aturan Notifikasi: Apresiasi Laporan Diterima')
                    ->description('Pesan penenang dan apresiasi otomatis ke kontak pelapor setelah laporan berhasil masuk.')
                    ->schema([
                        Toggle::make('spikap_apresiasi_is_active')
                            ->label('Aktifkan Notifikasi Apresiasi')
                            ->columnSpanFull(),
                        Select::make('spikap_apresiasi_targets')
                            ->label('Target Penerima Apresiasi')
                            ->multiple()
                            ->options([
                                'siswa'     => 'Siswa Pelapor',
                                'orang_tua' => 'Orang Tua / Wali Siswa',
                            ])
                            ->columnSpanFull(),
                        Textarea::make('spikap_apresiasi_siswa_template')
                            ->label('Template Apresiasi untuk Siswa')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_aplikasi}, {nama_siswa}, {jenis}, {tujuan_guru}')
                            ->columnSpanFull(),
                        Textarea::make('spikap_apresiasi_ortu_template')
                            ->label('Template Apresiasi untuk Orang Tua')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_aplikasi}, {nama_siswa}, {jenis}, {tujuan_guru}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Aturan Notifikasi: Perkembangan Penanganan Status')
                    ->description('Pemberitahuan otomatis ke pelapor saat status laporan diubah (misal: Diselidiki, Ditindaklanjuti, Selesai).')
                    ->schema([
                        Toggle::make('spikap_status_is_active')
                            ->label('Aktifkan Notifikasi Update Status Kasus')
                            ->columnSpanFull(),
                        Textarea::make('spikap_status_siswa_template')
                            ->label('Template Update Status untuk Siswa')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_aplikasi}, {nama_siswa}, {status_baru}, {catatan}')
                            ->columnSpanFull(),
                        Textarea::make('spikap_status_ortu_template')
                            ->label('Template Update Status untuk Orang Tua')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_aplikasi}, {nama_siswa}, {status_baru}, {catatan}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getGuruWaliWaTab(): Tab
    {
        return Tab::make('Notifikasi Guru Wali')
            ->icon('heroicon-o-user-group')
            ->schema([
                Section::make('Aturan Notifikasi: Siswa Bercerita / Pengajuan Bimbingan')
                    ->description('Sistem otomatis mengirimkan pesan WA ke Guru Wali asuh ketika siswa binaan mengajukan bimbingan atau menyampaikan cerita kendala.')
                    ->schema([
                        Toggle::make('guru_wali_pengajuan_is_active')
                            ->label('Aktifkan Notifikasi WA Pengajuan Cerita ke Guru Wali')
                            ->columnSpanFull(),
                        Textarea::make('guru_wali_pengajuan_template_pesan')
                            ->label('Template Pesan WA ke Guru Wali')
                            ->rows(7)
                            ->helperText('Placeholder: {nama_guru_wali}, {nama_siswa}, {kelas}, {kategori}, {mode}, {topik}, {detail}, {waktu}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Aturan Notifikasi: Tanggapan / Balasan Guru Wali')
                    ->description('Sistem otomatis mengirimkan pesan WA ke siswa dan/atau orang tua ketika Guru Wali membalas pesan obrolan.')
                    ->schema([
                        Toggle::make('guru_wali_balasan_is_active')
                            ->label('Aktifkan Notifikasi WA Balasan Guru Wali')
                            ->columnSpanFull(),
                        Select::make('guru_wali_balasan_targets')
                            ->label('Target Penerima Balasan')
                            ->multiple()
                            ->options([
                                'siswa'     => 'Siswa Binaan',
                                'orang_tua' => 'Orang Tua / Wali Siswa',
                            ])
                            ->columnSpanFull(),
                        Textarea::make('guru_wali_balasan_siswa_template')
                            ->label('Template Balasan untuk Siswa')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_siswa}, {nama_guru_wali}, {topik}, {balasan_guru}')
                            ->columnSpanFull(),
                        Textarea::make('guru_wali_balasan_ortu_template')
                            ->label('Template Balasan untuk Orang Tua')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_siswa}, {nama_guru_wali}, {topik}, {balasan_guru}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Aturan Notifikasi: Jadwal Pertemuan Tatap Muka')
                    ->description('Sistem otomatis mengirimkan pesan WA konfirmasi waktu & tempat bimbingan tatap muka yang ditetapkan Guru Wali.')
                    ->schema([
                        Toggle::make('guru_wali_jadwal_is_active')
                            ->label('Aktifkan Notifikasi WA Penetapan Jadwal Bimbingan')
                            ->columnSpanFull(),
                        Select::make('guru_wali_jadwal_targets')
                            ->label('Target Penerima Jadwal')
                            ->multiple()
                            ->options([
                                'siswa'     => 'Siswa Binaan',
                                'orang_tua' => 'Orang Tua / Wali Siswa',
                            ])
                            ->columnSpanFull(),
                        Textarea::make('guru_wali_jadwal_template')
                            ->label('Template Notifikasi Jadwal')
                            ->rows(6)
                            ->helperText('Placeholder: {nama_siswa}, {nama_guru_wali}, {topik}, {jadwal_pasti}, {catatan_guru}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function saveSpikapDanGuruWaliSettings(array $data): void
    {
        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'spikap_darurat'],
            [
                'is_active'      => $data['spikap_darurat_is_active'] ?? false,
                'recipients'     => $data['spikap_darurat_recipients'] ?? [],
                'template_pesan' => $data['spikap_darurat_template_pesan'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'spikap_biasa'],
            [
                'is_active'      => $data['spikap_biasa_is_active'] ?? false,
                'recipients'     => ['wali_kelas', 'guru_bk'],
                'template_pesan' => $data['spikap_biasa_template_pesan'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'spikap_apresiasi_siswa'],
            [
                'is_active'      => $data['spikap_apresiasi_is_active'] ?? false,
                'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                'template_pesan' => $data['spikap_apresiasi_siswa_template'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'spikap_apresiasi_ortu'],
            [
                'is_active'      => $data['spikap_apresiasi_is_active'] ?? false,
                'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                'template_pesan' => $data['spikap_apresiasi_ortu_template'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'spikap_status_siswa'],
            [
                'is_active'      => $data['spikap_status_is_active'] ?? false,
                'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                'template_pesan' => $data['spikap_status_siswa_template'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'spikap_status_ortu'],
            [
                'is_active'      => $data['spikap_status_is_active'] ?? false,
                'recipients'     => $data['spikap_apresiasi_targets'] ?? ['siswa'],
                'template_pesan' => $data['spikap_status_ortu_template'] ?? '',
            ]
        );

        try {
            $spikapModel = SpikapNotifSetting::instance();
            $daruratRecipients = $data['spikap_darurat_recipients'] ?? [];
            $spikapModel->update([
                'recipients'                 => $daruratRecipients,
                'emergency_handlers'         => $daruratRecipients,
                'notify_guru_laporan_biasa'  => $data['spikap_biasa_is_active'] ?? false,
                'notify_siswa_apresiasi'     => $data['spikap_apresiasi_is_active'] ?? false,
                'notify_siswa_tindak_lanjut' => $data['spikap_status_is_active'] ?? false,
                'target_penerima_siswa'      => $data['spikap_apresiasi_targets'] ?? ['siswa'],
            ]);
        } catch (\Throwable $e) {}

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'guru_wali_pengajuan'],
            [
                'is_active'      => $data['guru_wali_pengajuan_is_active'] ?? false,
                'recipients'     => ['guru_wali'],
                'template_pesan' => $data['guru_wali_pengajuan_template_pesan'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'guru_wali_balasan'],
            [
                'is_active'      => $data['guru_wali_balasan_is_active'] ?? false,
                'recipients'     => $data['guru_wali_balasan_targets'] ?? ['siswa'],
                'template_pesan' => $data['guru_wali_balasan_siswa_template'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'guru_wali_balasan_ortu'],
            [
                'is_active'      => $data['guru_wali_balasan_is_active'] ?? false,
                'recipients'     => $data['guru_wali_balasan_targets'] ?? ['siswa'],
                'template_pesan' => $data['guru_wali_balasan_ortu_template'] ?? '',
            ]
        );

        PresensiNotificationSetting::updateOrCreate(
            ['status_presensi' => 'guru_wali_jadwal'],
            [
                'is_active'      => $data['guru_wali_jadwal_is_active'] ?? false,
                'recipients'     => $data['guru_wali_jadwal_targets'] ?? ['siswa'],
                'template_pesan' => $data['guru_wali_jadwal_template'] ?? '',
            ]
        );
    }
}
