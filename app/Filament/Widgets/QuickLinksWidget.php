<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickLinksWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-links-widget';

    protected int | string | array $columnSpan = 'full';

    // Tempatkan di bawah PortalWidget
    protected static ?int $sort = 0;

    protected function getViewData(): array
    {
        $panelId = filament()->getCurrentPanel()->getId();
        
        $links = [];

        if ($panelId === 'admin') {
            $links = [
                [
                    'title' => 'Manajemen User',
                    'url' => url('/admin/users'),
                    'icon' => 'heroicon-o-users',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Pengaturan Sekolah',
                    'url' => url('/admin/pengaturan-sekolahs'),
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'color' => 'gray',
                ],
                [
                    'title' => 'Pengumuman',
                    'url' => url('/admin/pengumumen'),
                    'icon' => 'heroicon-o-megaphone',
                    'color' => 'info',
                ],
            ];
        } elseif ($panelId === 'admin-akademik') {
            $links = [
                [
                    'title' => 'Data Siswa',
                    'url' => url('/admin-akademik/siswas'),
                    'icon' => 'heroicon-o-user-group',
                    'color' => 'success',
                ],
                [
                    'title' => 'Data Guru',
                    'url' => url('/admin-akademik/gurus'),
                    'icon' => 'heroicon-o-briefcase',
                    'color' => 'info',
                ],
                [
                    'title' => 'Data Kelas',
                    'url' => url('/admin-akademik/kelas'),
                    'icon' => 'heroicon-o-home-modern',
                    'color' => 'warning',
                ],
                [
                    'title' => 'Tahun Ajaran',
                    'url' => url('/admin-akademik/tahun-ajarans'),
                    'icon' => 'heroicon-o-calendar',
                    'color' => 'danger',
                ],
            ];
        } elseif ($panelId === 'admin-presensi') {
            $settings = \App\Models\PengaturanSekolah::current();
            $scannerUrl = $settings?->barcode_scan_mode === 'nis' ? url('/scan-nis') : url('/scan');

            $links = [
                [
                    'title' => 'Buka Kios Scanner',
                    'url' => $scannerUrl,
                    'icon' => 'heroicon-o-qr-code',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Input Presensi Manual',
                    'url' => url('/admin-presensi/input-presensi-manual'),
                    'icon' => 'heroicon-o-pencil-square',
                    'color' => 'success',
                ],
                [
                    'title' => 'Rekap Absensi Kelas',
                    'url' => url('/admin-presensi/rekap-absensi-kelas'),
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'color' => 'info',
                ],
                [
                    'title' => 'Hari Libur',
                    'url' => url('/admin-presensi/hari-liburs'),
                    'icon' => 'heroicon-o-calendar-days',
                    'color' => 'danger',
                ],
            ];
        }

        return [
            'quickLinks' => $links,
        ];
    }
}
