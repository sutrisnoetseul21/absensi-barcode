<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Select;

class IconPickerField
{
    /**
     * Daftar icon FontAwesome yang tersedia untuk dipilih di admin.
     * Format: 'class FontAwesome' => 'Nama Deskriptif'
     */
    public static function icons(): array
    {
        return [
            // Orang & Sekolah
            'fas fa-users'           => '👥 Pengguna / Siswa',
            'fas fa-user-graduate'   => '🎓 Wisudawan',
            'fas fa-chalkboard-teacher' => '👨‍🏫 Guru',
            'fas fa-school'          => '🏫 Sekolah',
            'fas fa-book'            => '📚 Buku',
            'fas fa-book-open'       => '📖 Buku Terbuka',
            'fas fa-graduation-cap'  => '🎓 Kelulusan',
            'fas fa-pencil-alt'      => '✏️ Pensil',
            'fas fa-pen'             => '🖊️ Pena',
            'fas fa-clipboard'       => '📋 Clipboard',
            // Prestasi & Award
            'fas fa-award'           => '🏆 Penghargaan',
            'fas fa-trophy'          => '🥇 Trofi',
            'fas fa-medal'           => '🏅 Medali',
            'fas fa-star'            => '⭐ Bintang',
            // Info & Data
            'fas fa-chart-bar'       => '📊 Grafik Batang',
            'fas fa-chart-line'      => '📈 Grafik Garis',
            'fas fa-chart-pie'       => '🥧 Grafik Pie',
            'fas fa-info-circle'     => 'ℹ️ Info',
            'fas fa-calendar'        => '📅 Kalender',
            'fas fa-calendar-alt'    => '📆 Kalender Alt',
            'fas fa-clock'           => '🕐 Jam',
            // Gedung & Tempat
            'fas fa-building'        => '🏢 Gedung',
            'fas fa-home'            => '🏠 Rumah',
            'fas fa-map-marker-alt'  => '📍 Lokasi',
            'fas fa-landmark'        => '🏛️ Landmark',
            // Dokumen & File
            'fas fa-file-alt'        => '📄 Dokumen',
            'fas fa-file-pdf'        => '📕 PDF',
            'fas fa-file-excel'      => '📗 Excel',
            'fas fa-folder'          => '📁 Folder',
            'fas fa-download'        => '⬇️ Unduh',
            // Komunikasi & Link
            'fas fa-link'            => '🔗 Tautan',
            'fas fa-envelope'        => '✉️ Email',
            'fas fa-phone'           => '📞 Telepon',
            'fas fa-comment'         => '💬 Komentar',
            'fas fa-bullhorn'        => '📢 Pengumuman',
            'fab fa-whatsapp'        => '💬 WhatsApp',
            // Teknologi
            'fas fa-laptop'          => '💻 Laptop',
            'fas fa-desktop'         => '🖥️ Desktop',
            'fas fa-wifi'            => '📶 WiFi',
            'fas fa-qrcode'          => '▣ QR Code',
            'fas fa-barcode'         => '▦ Barcode',
            // Kesehatan
            'fas fa-heartbeat'       => '❤️ Detak Jantung',
            'fas fa-hospital'        => '🏥 Rumah Sakit',
            'fas fa-first-aid'       => '🩹 P3K',
            // Lainnya
            'fas fa-cog'             => '⚙️ Pengaturan',
            'fas fa-search'          => '🔍 Pencarian',
            'fas fa-bell'            => '🔔 Notifikasi',
            'fas fa-shield-alt'      => '🛡️ Keamanan',
            'fas fa-leaf'            => '🍃 Daun',
            'fas fa-recycle'         => '♻️ Daur Ulang',
            'fas fa-sun'             => '☀️ Matahari',
            'fas fa-flag'            => '🚩 Bendera',
            'fas fa-fingerprint'     => '👆 Fingerprint',
            'fas fa-id-card'         => '🪪 ID Card',
            'fas fa-bus'             => '🚌 Bus',
            'fas fa-mosque'          => '🕌 Masjid',
            'fas fa-pray'            => '🙏 Doa',
        ];
    }

    /**
     * Buat komponen Select icon picker siap pakai.
     */
    public static function make(string $fieldName = 'icon'): Select
    {
        return Select::make($fieldName)
            ->label('Icon')
            ->options(self::icons())
            ->searchable()
            ->allowHtml()
            ->helperText('Ketik untuk mencari icon. Pilih dari daftar icon FontAwesome yang tersedia.')
            ->required();
    }
}
