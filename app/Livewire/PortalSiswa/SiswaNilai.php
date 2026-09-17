<?php

namespace App\Livewire\PortalSiswa;

use App\Models\NilaiUjian;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SiswaNilai extends Component
{
    public function render()
    {
        $user = Auth::user();
        if (!$user || !$user->siswa) {
            abort(403, 'Akses ditolak: Anda tidak memiliki profil siswa.');
        }

        $siswa = $user->siswa;
        
        // Mengambil semua nilai ujian untuk siswa ini, beserta relasi UjianAkademik, Mapel, dan Tahun Ajaran
        $nilaiUjians = NilaiUjian::with([
                'ujianAkademik.mataPelajaran', 
                'ujianAkademik.jenisUjian', 
                'ujianAkademik.tahunAjaran'
            ])
            ->where('student_id', $siswa->id)
            ->whereHas('ujianAkademik', function ($query) {
                // Hanya ambil jika class_id (saat ujian dikerjakan) ada di dalam JSON array published_classes
                $query->whereRaw('JSON_CONTAINS(published_classes, JSON_QUOTE(nilai_ujians.class_id))');
            })
            ->get();

        // Group data berdasarkan Tahun Ajaran -> Event CBT
        $groupedNilai = [];
        foreach ($nilaiUjians as $nilai) {
            $ujian = $nilai->ujianAkademik;
            if (!$ujian) continue;

            $tahunAjaranName = $ujian->tahunAjaran ? $ujian->tahunAjaran->name : 'Tahun Ajaran Tidak Diketahui';
            $eventName = $ujian->cbt_event_nama ?: 'Event Lainnya';

            if (!isset($groupedNilai[$tahunAjaranName])) {
                $groupedNilai[$tahunAjaranName] = [];
            }
            if (!isset($groupedNilai[$tahunAjaranName][$eventName])) {
                $groupedNilai[$tahunAjaranName][$eventName] = [];
            }

            $groupedNilai[$tahunAjaranName][$eventName][] = [
                'mapel'       => $ujian->mataPelajaran ? $ujian->mataPelajaran->name : '-',
                'jenis_ujian' => $ujian->jenisUjian ? $ujian->jenisUjian->nama : '-',
                'nilai_akhir' => $nilai->nilai_akhir,
                'kkm'         => $ujian->kkm,
                'is_tuntas'   => $nilai->is_tuntas,
            ];
        }

        // Sort tahun ajaran descending
        krsort($groupedNilai);

        return view('livewire.portal-siswa.siswa-nilai', [
            'groupedNilai' => $groupedNilai,
        ])->layout('components.layouts.portal', ['title' => 'Nilai Akademik']);
    }
}
