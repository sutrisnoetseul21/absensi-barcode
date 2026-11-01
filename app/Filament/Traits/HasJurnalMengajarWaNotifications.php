<?php

namespace App\Filament\Traits;

use App\Models\PresensiNotificationSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\HtmlString;

trait HasJurnalMengajarWaNotifications
{
    /**
     * Memuat data pengaturan Notifikasi Jurnal Mengajar ke dalam array form state.
     */
    protected function loadJurnalMengajarData(array &$data): void
    {
        // ── 1. Saluran WhatsApp ──────────────────────────────────────────
        $waSetting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_wa")->first();
        $waWaliSetting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_wa_wali")->first();

        $defaultWaTemplateWali = "⚠️ *PEMBERITAHUAN KBM: SISWA MENINGGALKAN KELAS*\n\nYth. Wali Kelas {kelas},\nMenginformasikan bahwa siswa asuh Anda:\n- Nama: *{nama_siswa}*\n- NISN: {nisn}\n- Kelas: {kelas}\n\nTercatat *HADIR*, namun dinyatakan *TIDAK MENGIKUTI* pembelajaran:\n- Mata Pelajaran: *{mata_pelajaran}*\n- Guru Pengajar: {nama_guru}\n- Jam ke: {jam_ke}\n- Tanggal: {tanggal}\n- Catatan Guru: {keterangan}\n\nMohon untuk dapat ditindaklanjuti. Terima kasih.";

        $defaultWaTemplateOrtu = "⚠️ *PEMBERITAHUAN SEKOLAH: INFORMASI KEHADIRAN KELAS*\n\nYth. Bapak/Ibu Orang Tua/Wali dari *{nama_siswa}*,\n\nKami menginformasikan bahwa ananda tercatat hadir pagi ini, namun saat Kegiatan Belajar Mengajar (KBM) berlangsung, ananda tidak berada di kelas / tidak mengikuti pelajaran:\n- Mata Pelajaran: *{mata_pelajaran}*\n- Guru Pengajar: {nama_guru}\n- Jam ke: {jam_ke}\n- Tanggal: {tanggal}\n- Catatan Guru: {keterangan}\n\nPihak sekolah dan wali kelas sedang memastikan kondisi ananda di lingkungan sekolah. Terima kasih.";

        $data["jurnal_tidak_mengikuti_wa_active"]        = $waSetting ? (bool) $waSetting->is_active : true;
        $data["jurnal_tidak_mengikuti_wa_targets"]       = ($waSetting && !empty($waSetting->recipients)) ? $waSetting->recipients : ["wali_kelas", "orang_tua"];
        $data["jurnal_tidak_mengikuti_wa_template_wali"] = $waWaliSetting?->template_pesan ?: $defaultWaTemplateWali;
        $data["jurnal_tidak_mengikuti_wa_template_ortu"] = $waSetting?->template_pesan ?: $defaultWaTemplateOrtu;

        // ── 2. Saluran Notifikasi Aplikasi Android (FCM) ────────────────
        $appSetting      = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_app")->first();
        $appSiswaSetting = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_app_siswa")->first();
        $appWaliSetting  = PresensiNotificationSetting::where("status_presensi", "jurnal_tidak_mengikuti_app_wali")->first();

        $defaultAppTitleSiswa = "⚠️ Peringatan: Tidak Mengikuti Pelajaran";
        $defaultAppBodySiswa  = "Anda tercatat hadir tetapi tidak mengikuti KBM {mata_pelajaran} (Jam {jam_ke}). Segera temui guru pengajar atau wali kelas.";

        $defaultAppTitleWali = "⚠️ Siswa Meninggalkan Kelas: {nama_siswa}";
        $defaultAppBodyWali  = "{nama_siswa} ({kelas}) tercatat hadir namun tidak mengikuti KBM {mata_pelajaran} bersama {nama_guru}.";

        $data["jurnal_tidak_mengikuti_app_active"]         = $appSetting ? (bool) $appSetting->is_active : true;
        $data["jurnal_tidak_mengikuti_app_targets"]        = ($appSetting && !empty($appSetting->recipients)) ? $appSetting->recipients : ["siswa", "wali_kelas"];
        $data["jurnal_tidak_mengikuti_app_title_siswa"]    = $appSiswaSetting?->recipients["title"] ?? $defaultAppTitleSiswa;
        $data["jurnal_tidak_mengikuti_app_template_siswa"] = $appSiswaSetting?->template_pesan ?: $defaultAppBodySiswa;
        $data["jurnal_tidak_mengikuti_app_title_wali"]     = $appWaliSetting?->recipients["title"] ?? $defaultAppTitleWali;
        $data["jurnal_tidak_mengikuti_app_template_wali"]  = $appWaliSetting?->template_pesan ?: $defaultAppBodyWali;
    }

    /**
     * Menyusun Tab UI Notifikasi Jurnal Mengajar pada Schema Filament.
     */
    protected function getJurnalMengajarWaTab(): Tab
    {
        return Tab::make("Notifikasi Jurnal Mengajar")
            ->icon("heroicon-o-book-open")
            ->schema([
                // ── Seksi 1: Saluran WhatsApp ────────────────────────────
                Section::make("Saluran WhatsApp: Siswa Meninggalkan Kelas / Tidak Mengikuti KBM")
                    ->description("Pemberitahuan otomatis via WhatsApp ketika siswa tercatat HADIR/TERLAMBAT di gerbang pagi hari, namun ditandai Tidak Mengikuti oleh guru saat pelajaran berlangsung di kelas.")
                    ->schema([
                        Toggle::make("jurnal_tidak_mengikuti_wa_active")
                            ->label("Aktifkan Notifikasi WhatsApp")
                            ->helperText("Jika diaktifkan, WhatsApp akan dikirimkan otomatis saat jurnal mengajar disimpan di Web atau Aplikasi Android.")
                            ->columnSpanFull(),

                        Select::make("jurnal_tidak_mengikuti_wa_targets")
                            ->label("Kirim WhatsApp Ke (Pilihan Admin)")
                            ->multiple()
                            ->options([
                                "wali_kelas" => "👤 Wali Kelas Siswa (Pemberitahuan siswa asuh meninggalkan kelas)",
                                "orang_tua"  => "👨‍👩‍👧 Orang Tua / Wali Siswa (Pemberitahuan ananda tidak berada di kelas)",
                            ])
                            ->helperText("Admin dapat memilih untuk mengirim ke Wali Kelas saja, Orang Tua saja, atau keduanya sekaligus.")
                            ->columnSpanFull(),

                        Textarea::make("jurnal_tidak_mengikuti_wa_template_wali")
                            ->label("Template WhatsApp untuk Wali Kelas")
                            ->rows(8)
                            ->helperText("Placeholder: {nama_siswa}, {nisn}, {kelas}, {nama_guru}, {mata_pelajaran}, {jam_ke}, {tanggal}, {keterangan}")
                            ->columnSpanFull(),

                        Textarea::make("jurnal_tidak_mengikuti_wa_template_ortu")
                            ->label("Template WhatsApp untuk Orang Tua Siswa")
                            ->rows(8)
                            ->helperText("Placeholder: {nama_siswa}, {nisn}, {kelas}, {nama_guru}, {mata_pelajaran}, {jam_ke}, {tanggal}, {keterangan}")
                            ->columnSpanFull(),
                    ]),

                // ── Seksi 2: Saluran Aplikasi Android ────────────────────
                Section::make("Saluran Notifikasi Android: Status Bar & Inbox Aplikasi")
                    ->description("Push notifikasi langsung ke smartphone Android siswa dan wali kelas via Firebase Cloud Messaging (FCM HTTP v1) dan tersimpan di Pusat Notifikasi.")
                    ->schema([
                        Toggle::make("jurnal_tidak_mengikuti_app_active")
                            ->label("Aktifkan Notifikasi Aplikasi Android (FCM)")
                            ->helperText("Jika diaktifkan, notifikasi berdering/muncul di notification bar HP Android dan tersimpan di Inbox aplikasi.")
                            ->columnSpanFull(),

                        Select::make("jurnal_tidak_mengikuti_app_targets")
                            ->label("Kirim Notifikasi Aplikasi Ke (Pilihan Admin)")
                            ->multiple()
                            ->options([
                                "siswa"      => "🎓 Siswa (Peringatan & teguran langsung di HP Android siswa)",
                                "wali_kelas" => "👤 Wali Kelas (Peringatan di HP Android wali kelas)",
                            ])
                            ->helperText("Admin dapat memilih target penerima notifikasi di aplikasi Android.")
                            ->columnSpanFull(),

                        TextInput::make("jurnal_tidak_mengikuti_app_title_siswa")
                            ->label("Judul Notifikasi Aplikasi untuk Siswa")
                            ->columnSpanFull(),

                        Textarea::make("jurnal_tidak_mengikuti_app_template_siswa")
                            ->label("Isi Pesan Notifikasi Aplikasi untuk Siswa")
                            ->rows(3)
                            ->helperText("Placeholder: {nama_siswa}, {kelas}, {nama_guru}, {mata_pelajaran}, {jam_ke}")
                            ->columnSpanFull(),

                        TextInput::make("jurnal_tidak_mengikuti_app_title_wali")
                            ->label("Judul Notifikasi Aplikasi untuk Wali Kelas")
                            ->columnSpanFull(),

                        Textarea::make("jurnal_tidak_mengikuti_app_template_wali")
                            ->label("Isi Pesan Notifikasi Aplikasi untuk Wali Kelas")
                            ->rows(3)
                            ->helperText("Placeholder: {nama_siswa}, {kelas}, {nama_guru}, {mata_pelajaran}, {jam_ke}")
                            ->columnSpanFull(),
                    ]),

                // ── Seksi 3: Panduan Placeholder ─────────────────────────
                Section::make("Panduan Placeholder Dinamis")
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make("jurnal_placeholder_guide")
                            ->hiddenLabel()
                            ->content(new HtmlString('<div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400"><div><code>{nama_siswa}</code> : Nama lengkap siswa</div><div><code>{nisn}</code> : Nomor Induk Siswa Nasional</div><div><code>{kelas}</code> : Rombel / Kelas siswa (misal: IX-A)</div><div><code>{nama_guru}</code> : Nama guru mata pelajaran</div><div><code>{mata_pelajaran}</code> : Nama mata pelajaran KBM</div><div><code>{jam_ke}</code> : Jam pelajaran (misal: Jam ke 3 - 4)</div><div><code>{tanggal}</code> : Tanggal pelaksanaan KBM</div><div><code>{keterangan}</code> : Catatan keterangan khusus dari guru</div></div>'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Menyimpan seluruh pengaturan Notifikasi Jurnal Mengajar ke database presensi_notification_settings.
     */
    protected function saveJurnalMengajarSettings(array $data): void
    {
        // 1. Simpan Setting WhatsApp Umum / Ortu
        PresensiNotificationSetting::updateOrCreate(
            ["status_presensi" => "jurnal_tidak_mengikuti_wa"],
            [
                "is_active"      => $data["jurnal_tidak_mengikuti_wa_active"] ?? false,
                "recipients"     => $data["jurnal_tidak_mengikuti_wa_targets"] ?? ["wali_kelas", "orang_tua"],
                "template_pesan" => $data["jurnal_tidak_mengikuti_wa_template_ortu"] ?? "",
            ]
        );

        // 2. Simpan Setting WhatsApp Wali Kelas
        PresensiNotificationSetting::updateOrCreate(
            ["status_presensi" => "jurnal_tidak_mengikuti_wa_wali"],
            [
                "is_active"      => $data["jurnal_tidak_mengikuti_wa_active"] ?? false,
                "recipients"     => ["wali_kelas"],
                "template_pesan" => $data["jurnal_tidak_mengikuti_wa_template_wali"] ?? "",
            ]
        );

        // 3. Simpan Setting Notifikasi Aplikasi Utama
        PresensiNotificationSetting::updateOrCreate(
            ["status_presensi" => "jurnal_tidak_mengikuti_app"],
            [
                "is_active"      => $data["jurnal_tidak_mengikuti_app_active"] ?? false,
                "recipients"     => $data["jurnal_tidak_mengikuti_app_targets"] ?? ["siswa", "wali_kelas"],
                "template_pesan" => $data["jurnal_tidak_mengikuti_app_template_siswa"] ?? "",
            ]
        );

        // 4. Simpan Setting Notifikasi Aplikasi Siswa (Judul + Body)
        PresensiNotificationSetting::updateOrCreate(
            ["status_presensi" => "jurnal_tidak_mengikuti_app_siswa"],
            [
                "is_active"      => $data["jurnal_tidak_mengikuti_app_active"] ?? false,
                "recipients"     => [
                    "title" => $data["jurnal_tidak_mengikuti_app_title_siswa"] ?? "⚠️ Peringatan: Tidak Mengikuti Pelajaran",
                ],
                "template_pesan" => $data["jurnal_tidak_mengikuti_app_template_siswa"] ?? "",
            ]
        );

        // 5. Simpan Setting Notifikasi Aplikasi Wali Kelas (Judul + Body)
        PresensiNotificationSetting::updateOrCreate(
            ["status_presensi" => "jurnal_tidak_mengikuti_app_wali"],
            [
                "is_active"      => $data["jurnal_tidak_mengikuti_app_active"] ?? false,
                "recipients"     => [
                    "title" => $data["jurnal_tidak_mengikuti_app_title_wali"] ?? "⚠️ Siswa Meninggalkan Kelas: {nama_siswa}",
                ],
                "template_pesan" => $data["jurnal_tidak_mengikuti_app_template_wali"] ?? "",
            ]
        );
    }
}
