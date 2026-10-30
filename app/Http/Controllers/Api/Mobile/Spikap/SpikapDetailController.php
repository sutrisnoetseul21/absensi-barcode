<?php

namespace App\Http\Controllers\Api\Mobile\Spikap;

use App\Models\SpikapLampiran;
use App\Models\SpikapLaporan;
use App\Models\SpikapLogStatus;
use App\Services\SpikapNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SpikapDetailController extends SpikapBaseController
{
    /**
     * GET /api/v1/guru/spikap/laporan/{id}
     * Mengambil detail lengkap investigasi 1 kasus pengaduan SPIKAP.
     */
    public function detail(Request $request, $id): JsonResponse
    {
        $authResult = $this->requireSpikapAccess($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        $laporan = SpikapLaporan::forUser($user)
            ->with([
                'siswa.enrollmentAktif.kelas',
                'lampiran',
                'logStatus.changedBy',
                'lastHandledBy',
            ])
            ->find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan aduan tidak ditemukan atau Anda tidak memiliki hak akses untuk kasus ini.',
            ], 404);
        }

        $siswa = $laporan->siswa;
        $kelas = $siswa?->enrollmentAktif?->kelas;

        $canUpdate = $laporan->canUpdateStatusBy($user);

        $statusLabels = [
            'diterima' => 'Diterima',
            'dalam_investigasi' => 'Dalam Investigasi',
            'selesai' => 'Selesai',
        ];

        $jenisLabels = [
            'fisik' => 'Kekerasan Fisik',
            'verbal' => 'Kekerasan Verbal',
            'sosial' => 'Kekerasan Sosial / Relasional',
            'digital' => 'Perundungan Siber (Digital)',
            'lainnya' => 'Lainnya',
        ];

        // Format berkas lampiran
        $lampirans = $laporan->lampiran->map(function (SpikapLampiran $lamp) use ($request) {
            $identifierParam = $request->input('identifier') ? '?identifier=' . $request->input('identifier') : '';
            return [
                'id' => $lamp->id,
                'tipe_file' => $lamp->tipe_file, // 'foto' atau 'video'
                'nama_asli' => $lamp->nama_asli,
                'ukuran_bytes' => $lamp->ukuran_bytes,
                'ukuran_label' => $this->formatBytes($lamp->ukuran_bytes),
                'url' => url("/api/v1/guru/spikap/lampiran/{$lamp->id}{$identifierParam}"),
            ];
        });

        // Format timeline audit trail status log
        $logs = $laporan->logStatus->sortByDesc('created_at')->values()->map(function (SpikapLogStatus $log) use ($statusLabels) {
            $changedBy = $log->changedBy;
            return [
                'id' => $log->id,
                'status_lama' => $log->status_lama,
                'status_lama_label' => $statusLabels[$log->status_lama] ?? ($log->status_lama ?: '-'),
                'status_baru' => $log->status_baru,
                'status_baru_label' => $statusLabels[$log->status_baru] ?? ucfirst($log->status_baru),
                'catatan' => $log->catatan ?: 'Tanpa catatan tambahan',
                'changed_by_name' => $changedBy ? $changedBy->name : 'Sistem Otomatis',
                'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : null,
                'created_at_formatted' => $log->created_at ? $log->created_at->format('d M Y, H:i') : '-',
                'waktu_relatif' => $log->created_at ? $log->created_at->diffForHumans() : '-',
            ];
        });

        // Kontak Ortu & Siswa
        $rawHpOrtu = $siswa?->no_hp_orang_tua;
        $waHpOrtu = null;
        if ($rawHpOrtu) {
            $clean = preg_replace('/[^0-9]/', '', $rawHpOrtu);
            if (str_starts_with($clean, '08')) {
                $clean = '628' . substr($clean, 2);
            }
            $waHpOrtu = $clean;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $laporan->id,
                'ticket_number' => '#SPK-' . str_pad($laporan->id, 4, '0', STR_PAD_LEFT),
                'student' => [
                    'id' => (string) ($siswa?->id ?? ''),
                    'name' => $siswa?->name ?? 'Siswa Tidak Diketahui',
                    'nisn' => $siswa?->nisn ?? '-',
                    'gender' => $siswa?->gender ?? '-',
                    'class_name' => $kelas?->name ?? '-',
                    'nama_ayah' => $siswa?->nama_ayah,
                    'nama_ibu' => $siswa?->nama_ibu,
                    'no_hp' => $siswa?->no_hp,
                    'no_hp_orang_tua' => $rawHpOrtu,
                    'wa_hp_ortu' => $waHpOrtu,
                    'photo_url' => $siswa?->photo_url,
                ],
                'sifat_laporan' => $laporan->sifat_laporan,
                'is_darurat' => $laporan->sifat_laporan === 'darurat',
                'sifat_label' => $laporan->sifat_laporan === 'darurat' ? '🚨 Darurat' : 'Biasa',
                'jenis_perundungan' => $laporan->jenis_perundungan,
                'jenis_label' => $jenisLabels[$laporan->jenis_perundungan] ?? ucfirst($laporan->jenis_perundungan),
                'status' => $laporan->status,
                'status_label' => $statusLabels[$laporan->status] ?? ucfirst($laporan->status),
                'uraian_kejadian' => $laporan->uraian_kejadian,
                'lokasi_kejadian' => $laporan->lokasi_kejadian ?: 'Lingkungan Sekolah',
                'waktu_kejadian' => $laporan->waktu_kejadian ? $laporan->waktu_kejadian->format('d M Y, H:i') : null,
                'waktu_kejadian_raw' => $laporan->waktu_kejadian ? $laporan->waktu_kejadian->format('Y-m-d H:i:s') : null,
                'created_at' => $laporan->created_at ? $laporan->created_at->format('d M Y, H:i') : null,
                'created_at_relatif' => $laporan->created_at ? $laporan->created_at->diffForHumans() : '-',
                'last_handled_by_name' => $laporan->lastHandledBy?->name,
                'can_update_status' => $canUpdate,
                'lampiran' => $lampirans,
                'audit_logs' => $logs,
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/spikap/laporan/{id}/status
     * Memperbarui status investigasi laporan, mencatat log audit, dan kirim notifikasi WA.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $authResult = $this->requireSpikapAccess($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        $request->validate([
            'status' => 'required|in:diterima,dalam_investigasi,selesai',
            'catatan' => 'nullable|string|max:1000',
            'notify_whatsapp' => 'nullable|boolean',
        ], [
            'status.required' => 'Pilih status baru laporan.',
            'status.in' => 'Pilihan status penanganan tidak valid.',
            'catatan.max' => 'Catatan penanganan tidak boleh melebihi 1000 karakter.',
        ]);

        $laporan = SpikapLaporan::forUser($user)->find($id);
        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan atau Anda tidak berwenang atas laporan ini.',
            ], 404);
        }

        if (!$laporan->canUpdateStatusBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak wewenang untuk mengubah status laporan ini.',
            ], 403);
        }

        $statusLama = $laporan->status;
        $statusBaru = (string) $request->input('status');
        $catatanTrim = trim((string) $request->input('catatan', ''));
        $notifyWa = $request->boolean('notify_whatsapp', true);

        if ($statusBaru === $statusLama && empty($catatanTrim)) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada perubahan status atau catatan baru yang disimpan.',
            ]);
        }

        $teacher = $this->resolveTeacher($request);

        DB::transaction(function () use ($laporan, $statusLama, $statusBaru, $catatanTrim, $user, $teacher) {
            // 1. Update status laporan & guru penangan terakhir
            $laporan->update([
                'status' => $statusBaru,
                'last_handled_by' => $teacher?->id,
            ]);

            // 2. Catat audit trail di spikap_log_status
            SpikapLogStatus::catatGuru(
                $laporan->id,
                $statusLama,
                $statusBaru,
                $catatanTrim ?: null,
                $user->id
            );
        });

        // 3. Kirim notifikasi WA perkembangan kasus ke orang tua / siswa jika diaktifkan
        $waSent = false;
        if ($notifyWa) {
            try {
                $service = app(SpikapNotificationService::class);
                $service->sendStatusUpdateNotificationToSiswaDanOrtu(
                    $laporan,
                    $statusBaru,
                    $catatanTrim ?: null
                );
                $waSent = true;
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'Status laporan berhasil diperbarui.',
            'data' => [
                'id' => $laporan->id,
                'status' => $statusBaru,
                'wa_notif_sent' => $waSent,
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/spikap/lampiran/{id}
     * Streaming / preview berkas bukti lampiran kasus secara aman.
     */
    public function lampiran(Request $request, $id): Response
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $lampiran = SpikapLampiran::find($id);
        if (!$lampiran) {
            abort(404, 'Berkas bukti tidak ditemukan.');
        }

        // Pastikan user memiliki hak akses ke laporan terkait
        $hasAccess = SpikapLaporan::forUser($user)->where('id', $lampiran->laporan_id)->exists();
        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat bukti kasus ini.');
        }

        if (!Storage::disk('local')->exists($lampiran->path_file)) {
            abort(404, 'File lampiran tidak tersedia di server.');
        }

        return Storage::disk('local')->response($lampiran->path_file, $lampiran->nama_asli);
    }

    /**
     * Helper pemformat ukuran file.
     */
    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
