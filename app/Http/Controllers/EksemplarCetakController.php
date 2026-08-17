<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\EksemplarBuku;
use Illuminate\Http\Request;

class EksemplarCetakController extends Controller
{
    public function cetakBarcode(Buku $buku)
    {
        $eksemplars = $buku->eksemplarBukus()->orderBy('kode_eksemplar')->get();
        
        if ($eksemplars->isEmpty()) {
            abort(404, 'Buku ini belum memiliki eksemplar.');
        }

        return view('pdf.label-barcode-eksemplar', [
            'eksemplars' => $eksemplars,
            'buku' => $buku,
        ]);
    }

    public function cetakBarcodeEksemplar(EksemplarBuku $eksemplar)
    {
        return view('pdf.label-barcode-eksemplar', [
            'eksemplars' => collect([$eksemplar]),
            'buku' => $eksemplar->buku,
        ]);
    }

    public function cetakBarcodeMassal(Request $request)
    {
        $sessionKey = $request->query('session_key');
        if (empty($sessionKey)) {
            abort(400, 'Parameter session_key tidak ditemukan');
        }

        $ids = session()->get($sessionKey);
        if (empty($ids) || !is_array($ids)) {
            abort(404, 'Data session cetak telah kedaluwarsa atau tidak valid. Silakan ulangi proses cetak.');
        }

        // Opsional: Hapus dari session setelah diambil agar tidak menumpuk, 
        // tapi jika user merefresh halaman maka akan error. Sebaiknya biarkan expire sesuai config session.
        // session()->forget($sessionKey);

        $eksemplars = EksemplarBuku::with('buku')->whereIn('id', $ids)->orderBy('kode_eksemplar')->get();

        if ($eksemplars->isEmpty()) {
            abort(404, 'Data eksemplar tidak ditemukan');
        }

        return view('pdf.label-barcode-eksemplar', [
            'eksemplars' => $eksemplars,
            'buku' => null, // Karena bisa campuran dari beberapa buku jika diakses global, tapi konteksnya dari 1 buku
        ]);
    }

    public function cetakLabelSpine(Buku $buku, Request $request)
    {
        $query = $buku->eksemplarBukus()->orderBy('kode_eksemplar');
        if ($request->has('jumlah')) {
            $jumlah = max((int) $request->query('jumlah'), 1);
            $query->limit($jumlah);
        }
        $eksemplars = $query->get();
        
        if ($eksemplars->isEmpty()) {
            abort(404, 'Buku ini belum memiliki eksemplar.');
        }

        return view('pdf.label-spine-buku', [
            'eksemplars' => $eksemplars,
            'buku' => $buku,
        ]);
    }

    public function cetakLabelSpineEksemplar(EksemplarBuku $eksemplar)
    {
        return view('pdf.label-spine-buku', [
            'eksemplars' => collect([$eksemplar]),
            'buku' => $eksemplar->buku,
        ]);
    }

    public function cetakLabelSpineMassal(Request $request)
    {
        $sessionKey = $request->query('session_key');
        if (empty($sessionKey)) {
            abort(400, 'Parameter session_key tidak ditemukan');
        }

        $ids = session()->get($sessionKey);
        if (empty($ids) || !is_array($ids)) {
            abort(404, 'Data session cetak telah kedaluwarsa atau tidak valid. Silakan ulangi proses cetak.');
        }

        $eksemplars = EksemplarBuku::with('buku')->whereIn('id', $ids)->orderBy('kode_eksemplar')->get();

        if ($eksemplars->isEmpty()) {
            abort(404, 'Data eksemplar tidak ditemukan');
        }

        return view('pdf.label-spine-buku', [
            'eksemplars' => $eksemplars,
            'buku' => null,
        ]);
    }

    public function cetakLabelGabungan(Buku $buku, Request $request)
    {
        $query = $buku->eksemplarBukus()->orderBy('kode_eksemplar');
        if ($request->has('jumlah')) {
            $jumlah = max((int) $request->query('jumlah'), 1);
            $query->limit($jumlah);
        }
        $eksemplars = $query->get();
        
        if ($eksemplars->isEmpty()) {
            abort(404, 'Buku ini belum memiliki eksemplar.');
        }

        return view('pdf.label-gabungan-buku', [
            'eksemplars' => $eksemplars,
            'buku' => $buku,
        ]);
    }

    public function cetakLabelGabunganEksemplar(EksemplarBuku $eksemplar)
    {
        return view('pdf.label-gabungan-buku', [
            'eksemplars' => collect([$eksemplar]),
            'buku' => $eksemplar->buku,
        ]);
    }

    public function cetakLabelGabunganMassal(Request $request)
    {
        $sessionKey = $request->query('session_key');
        if (empty($sessionKey)) {
            abort(400, 'Parameter session_key tidak ditemukan');
        }

        $ids = session()->get($sessionKey);
        if (empty($ids) || !is_array($ids)) {
            abort(404, 'Data session cetak telah kedaluwarsa atau tidak valid. Silakan ulangi proses cetak.');
        }

        $eksemplars = EksemplarBuku::with('buku')->whereIn('id', $ids)->orderBy('kode_eksemplar')->get();

        if ($eksemplars->isEmpty()) {
            abort(404, 'Data eksemplar tidak ditemukan');
        }

        return view('pdf.label-gabungan-buku', [
            'eksemplars' => $eksemplars,
            'buku' => null,
        ]);
    }
}
