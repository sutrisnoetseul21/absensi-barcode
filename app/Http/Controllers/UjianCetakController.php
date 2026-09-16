<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\UjianAkademik;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;

class UjianCetakController extends Controller
{
    /**
     * Cetak massal kartu peserta ujian CBT untuk satu ujian akademik.
     * Dapat difilter per kelas jika query parameter `class_id` disertakan.
     */
    public function cetakKartu(UjianAkademik $ujian, Request $request)
    {
        $settings = PengaturanSekolah::current();
        $selectedClassId = $request->query('class_id');

        // Ambil rombel sasaran
        $classesQuery = $ujian->classes();
        if ($selectedClassId) {
            $classesQuery->where('classes.id', $selectedClassId);
        }
        $classes = $classesQuery->orderBy('name')->get();

        $defaultPassword = config('cbt.drivers.zencbt.default_password', 'Spentika');
        $barcodeGenerator = new BarcodeGeneratorPNG();

        // Kumpulkan data kartu peserta
        $cardsData = [];

        foreach ($classes as $kelas) {
            $students = Siswa::where('status', 'aktif')
                ->whereHas('enrollments', function ($q) use ($kelas, $ujian) {
                    $q->where('class_id', $kelas->id);
                    if ($ujian->academic_year_id) {
                        $q->where('academic_year_id', $ujian->academic_year_id);
                    }
                })
                ->with(['cbtProfile'])
                ->orderBy('name')
                ->get();

            foreach ($students as $student) {
                $cbtProfile = $student->cbtProfile;
                $password = $cbtProfile?->cbt_password ?: $defaultPassword;
                $sesi = $cbtProfile?->cbt_sesi ?: 'Sesi 1';
                $ruang = 'Ruang Lab CBT';

                // Barcode PNG Base64
                $barcodeBase64 = null;
                try {
                    $barcodeRaw = $barcodeGenerator->getBarcode($student->nisn, $barcodeGenerator::TYPE_CODE_128, 2, 45);
                    $barcodeBase64 = 'data:image/png;base64,' . base64_encode($barcodeRaw);
                } catch (\Exception $e) {
                    $barcodeBase64 = null;
                }

                // Foto Siswa Base64
                $photoBase64 = null;
                if ($student->photo_path && file_exists(public_path('storage/' . $student->photo_path))) {
                    $photoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('storage/' . $student->photo_path)));
                }

                $cardsData[] = [
                    'student'        => $student,
                    'class_name'     => $kelas->name,
                    'password'       => $password,
                    'sesi'           => $sesi,
                    'ruang'          => $ruang,
                    'barcode_base64' => $barcodeBase64,
                    'photo_base64'   => $photoBase64,
                ];
            }
        }

        return view('pdf.kartu-ujian-cbt-massal', [
            'ujian'           => $ujian,
            'settings'        => $settings,
            'allClasses'      => $ujian->classes,
            'selectedClassId' => $selectedClassId,
            'cardsData'       => $cardsData,
        ]);
    }
}
