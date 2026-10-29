<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSekolah;
use Illuminate\Http\JsonResponse;

class SchoolInfoController extends Controller
{
    /**
     * Mengambil informasi identitas lembaga sekolah (Publik).
     * Menyediakan nama sekolah, logo_url dengan header CORS, serta logo_base64
     * berukuran optimal agar gambar logo langsung tampil instan di Flutter Web & Android.
     */
    public function index(): JsonResponse
    {
        try {
            $settings = PengaturanSekolah::current();

            if (!$settings) {
                return response()->json([
                    'success' => true,
                    'message' => 'Default school info',
                    'data' => [
                        'nama_sekolah' => config('app.name', 'SMP Negeri 3 Kedungreja'),
                        'npsn' => '',
                        'alamat' => '',
                        'logo_url' => null,
                        'logo_base64' => null,
                        'theme_primary' => '#1E40AF',
                        'theme_secondary' => '#3B82F6',
                    ],
                ]);
            }

            $logoUrl = null;
            $logoBase64 = null;

            if ($settings->school_logo_path) {
                $localPath = storage_path('app/public/' . $settings->school_logo_path);

                if (file_exists($localPath)) {
                    // Buat versi thumbnail ringan (lebar maks 240px) agar base64 ringan & instan di-decode
                    $logoBase64 = $this->getOptimizedBase64Logo($localPath);
                    $logoUrl = url('/api/v1/school-logo');
                } else {
                    $logoUrl = asset('storage/' . $settings->school_logo_path);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Informasi sekolah berhasil dimuat',
                'data' => [
                    'nama_sekolah' => $settings->school_name ?? config('app.name', 'SMP Negeri 3 Kedungreja'),
                    'npsn' => $settings->npsn ?? '',
                    'alamat' => $settings->school_address ?? '',
                    'logo_url' => $logoUrl,
                    'logo_base64' => $logoBase64,
                    'theme_primary' => $settings->theme_primary ?? '#1E40AF',
                    'theme_secondary' => $settings->theme_secondary ?? '#3B82F6',
                    'fitur_aktif' => [
                        'sholat_dhuhur' => (bool) $settings->enable_sholat_dhuhur,
                        'sholat_jumat' => (bool) $settings->enable_sholat_jumat,
                        'checkout' => (bool) $settings->enable_checkout,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat profil sekolah: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengoptimalkan ukuran gambar logo menjadi lebar maks 240px
     * agar proses decode di Flutter Web (CPU rendering) berlangsung instan (< 10ms).
     */
    private function getOptimizedBase64Logo(string $localPath): string
    {
        try {
            $cachePath = dirname($localPath) . '/thumb_240_' . basename($localPath);

            if (!file_exists($cachePath) || filemtime($cachePath) < filemtime($localPath)) {
                $info = @getimagesize($localPath);
                if ($info && $info[0] > 240 && function_exists('imagecreatetruecolor')) {
                    $mime = $info['mime'];
                    $src = match ($mime) {
                        'image/png' => @imagecreatefrompng($localPath),
                        'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($localPath),
                        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($localPath) : null,
                        default => null,
                    };

                    if ($src) {
                        $w = imagesx($src);
                        $h = imagesy($src);
                        $newW = 240;
                        $newH = (int) round($h * ($newW / $w));

                        $dst = imagecreatetruecolor($newW, $newH);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
                        @imagepng($dst, $cachePath, 6);
                        imagedestroy($src);
                        imagedestroy($dst);
                    }
                }
            }

            if (file_exists($cachePath)) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents($cachePath));
            }
        } catch (\Throwable) {
            // Lanjut ke fallback file asli jika gagal resize
        }

        $mime = mime_content_type($localPath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localPath));
    }

    /**
     * Menyajikan file logo sekolah secara langsung dengan header CORS resmi (*).
     */
    public function logo()
    {
        $settings = PengaturanSekolah::current();
        if (!$settings || !$settings->school_logo_path) {
            return response('', 404);
        }

        $localPath = storage_path('app/public/' . $settings->school_logo_path);
        if (!file_exists($localPath)) {
            return response('', 404);
        }

        return response()->file($localPath, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
