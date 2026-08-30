<?php

namespace App\Livewire\PortalGuru;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use App\Exports\SiswaUpdateDataByClassExport;
use App\Exports\SiswaUpdateNoHpByClassExport;
use App\Imports\SiswaUpdateDataImport;
use App\Imports\SiswaUpdateNoHpImport;
use Illuminate\Support\Facades\Storage;

class DataSiswaList extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Filters
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classes = [];
    public $selectedClassId = null;
    public bool $hasSubmittedFilter = false;

    // Search & Select
    public $search = '';
    public $selectAll = false;
    public $selectedStudents = [];

    // Edit Data
    public $showEditDataModal = false;
    public $editDataStudentId = null;
    public $editDataNisn = '';
    public $editDataNis = '';
    public $editDataName = '';
    public $editDataBirthPlace = '';
    public $editDataBirthDate = '';
    public $editDataAddress = '';
    public $editDataNoHp = '';

    // Edit No HP Only
    public $showEditNoHpModal = false;
    public $editNoHpStudentId = null;
    public $editNoHpSiswa = '';
    public $editNoHpOrtu = '';

    // Change Password
    public $showChangePasswordModal = false;
    public $changePasswordStudentId = null;
    public $newPassword = '';
    public $newPasswordConfirmation = '';

    // Upload Data
    public $showUploadModal = false;
    public $uploadType = 'data';
    public $uploadFile;
    public $previewRows = [];
    public $previewSummary = [];
    public $importError = '';
    public $isProcessingImport = false;

    public function mount(): void
    {
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes = collect();
            $this->selectedClassId = null;
            return;
        }
        
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'admin_presensi'])) {
            if ($user->hasRole('wali_kelas') && $user->teacher === null) {
                abort(403, 'Akses Ditolak: Data profil guru Anda belum ditautkan oleh Admin.');
            }
        }

        $isAdminMode = $user->hasAnyRole(['super_admin', 'admin_presensi']);
        $hasBypass   = $user->can('portal_guru:akses_semua_kelas');

        if (!$isAdminMode && !$hasBypass) {
            $actor = $user->teacher;
            $this->classes = Kelas::whereHas('kelasAjarans', function ($query) use ($actor) {
                $query->where('academic_year_id', $this->selectedAcademicYearId)
                        ->where('teacher_id', $actor->id);
            })->orderBy('name', 'asc')->get();
        } else {
            $this->classes = Kelas::whereHas('kelasAjarans', function ($query) {
                $query->where('academic_year_id', $this->selectedAcademicYearId);
            })->orderBy('name', 'asc')->get();
        }

        if ($this->classes->isNotEmpty()) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = collect($this->classes)->first()->id;
            }
        } else {
            $this->selectedClassId = null;
        }
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->hasSubmittedFilter = false;
        $this->loadClasses();
    }

    public function updatedSelectedClassId(): void
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'admin_presensi'])) {
            if ($user->hasRole('wali_kelas') && $user->teacher === null) {
                abort(403, 'Akses Ditolak: Data profil guru tidak lengkap.');
            }
        }

        $isAdminMode = $user->hasAnyRole(['super_admin', 'admin_presensi']);
        $hasBypass   = $user->can('portal_guru:akses_semua_kelas');
        
        if (!$isAdminMode && !$hasBypass) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                abort(403, 'Unauthorized action. Anda tidak memiliki akses ke kelas ini.');
            }
        }
        $this->hasSubmittedFilter = false;
    }

    public function filterData(): void
    {
        if (!$this->selectedClassId) {
            session()->flash('warning', 'Silakan pilih Kelas terlebih dahulu sebelum memproses.');
            return;
        }

        $this->hasSubmittedFilter = true;
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = $this->getStudentsQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function getStudentsQuery()
    {
        if (!$this->hasSubmittedFilter || !$this->selectedClassId) {
            return Siswa::query()->whereRaw('1 = 0');
        }

        return Siswa::query()
            ->where('status', 'aktif')
            ->whereHas('enrollments', function ($q) {
                $q->where('class_id', $this->selectedClassId)
                  ->where('academic_year_id', $this->selectedAcademicYearId)
                  ->where('status', 'aktif');
            })
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('nisn', 'like', '%' . $this->search . '%')
                          ->orWhere('nis', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name');
    }

    public function getStudentsProperty()
    {
        return $this->getStudentsQuery()->paginate(15);
    }

    public function cetakKartu(string $studentId)
    {
        $url = route('siswa.cetak-kartu-login', $studentId);
        $this->dispatch('open-url', url: $url);
    }

    public function cetakTerpilih()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Tidak ada siswa yang dipilih.');
            return;
        }

        $ids = implode(',', $this->selectedStudents);
        $url = route('siswa.cetak-kartu-login-massal', ['ids' => $ids]);
        $this->dispatch('open-url', url: $url);
    }

    public function cetakSemua()
    {
        $records = $this->getStudentsQuery()->get();
        if ($records->isEmpty()) {
            session()->flash('error', 'Tidak ada data siswa untuk kelas ini.');
            return;
        }

        $ids = $records->pluck('id')->implode(',');
        $url = route('siswa.cetak-kartu-login-massal', ['ids' => $ids]);
        $this->dispatch('open-url', url: $url);
    }

    // --- FITUR EXPORT / UPDATE EXCEL ---

    public function downloadDataExcel()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            session()->flash('warning', 'Silakan pilih kelas terlebih dahulu.');
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $safeClassName = str_replace(['/', '\\'], '-', $kelas?->name ?? 'Kelas');
        $fileName = 'Data_Siswa_' . $safeClassName . '.xlsx';

        return Excel::download(new SiswaUpdateDataByClassExport($this->selectedClassId, $this->selectedAcademicYearId), $fileName);
    }

    public function downloadNoHpExcel()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            session()->flash('warning', 'Silakan pilih kelas terlebih dahulu.');
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $safeClassName = str_replace(['/', '\\'], '-', $kelas?->name ?? 'Kelas');
        $fileName = 'Update_NoHP_Siswa_' . $safeClassName . '.xlsx';

        return Excel::download(new SiswaUpdateNoHpByClassExport($this->selectedClassId, $this->selectedAcademicYearId), $fileName);
    }

    public function openUploadModal($type = 'data')
    {
        $this->uploadType = $type;
        $this->resetUploadState();
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->resetUploadState();
    }

    private function resetUploadState()
    {
        $this->uploadFile = null;
        $this->previewRows = [];
        $this->previewSummary = [];
        $this->importError = '';
        $this->isProcessingImport = false;
    }

    public function updatedUploadFile()
    {
        $this->previewRows = [];
        $this->previewSummary = [];
        $this->importError = '';

        if (!$this->uploadFile) return;

        try {
            $filePath = $this->uploadFile->getRealPath();
            $data = Excel::toArray(new \stdClass, $filePath);
            
            if (empty($data[0])) {
                $this->importError = 'File Excel kosong.';
                return;
            }

            $sheet = $data[0];
            $headers = $sheet[0] ?? [];
            
            if (strtolower(trim((string)($headers[0] ?? ''))) !== 'id') {
                $this->importError = 'Format berkas salah. Kolom pertama harus berupa ID. Pastikan Anda mengunggah file hasil dari tombol Download Data (Excel).';
                return;
            }

            $allRows = array_slice($sheet, 1);
            $rows = array_filter($allRows, fn($row) => trim((string)($row[0] ?? '')) !== '');
            
            if (empty($rows)) {
                $this->importError = 'Tidak ada baris data siswa yang terisi ID-nya.';
                return;
            }

            foreach (array_slice($rows, 0, 100) as $row) {
                $id   = trim((string)($row[0] ?? ''));
                $name = trim((string)($row[1] ?? ''));

                if ($this->uploadType === 'data') {
                    $nisn = trim((string)($row[1] ?? '')); // Wait, in full data Name is col 3
                    $name = trim((string)($row[3] ?? ''));
                    $this->previewRows[] = [
                        'id' => substr($id, 0, 8) . '...',
                        'col1' => $nisn,
                        'col2' => $name,
                        'status' => 'Siap Diperiksa',
                    ];
                } else {
                    $name = trim((string)($row[1] ?? '')); // In No HP, Name is col 1
                    $hp_siswa = trim((string)($row[2] ?? ''));
                    $hp_ortu = trim((string)($row[3] ?? ''));
                    
                    $this->previewRows[] = [
                        'id' => substr($id, 0, 8) . '...',
                        'col1' => $name,
                        'col2' => "Siswa: $hp_siswa, Ortu: $hp_ortu",
                        'status' => 'Siap Diperiksa',
                    ];
                }
            }

            $this->previewSummary = [
                'total' => count($rows),
                'truncated' => count($rows) > 100
            ];

        } catch (\Exception $e) {
            $this->importError = 'Gagal membaca file: ' . $e->getMessage();
        }
    }

    public function processUpload()
    {
        if (!$this->uploadFile) {
            $this->importError = 'Silakan pilih file Excel terlebih dahulu.';
            return;
        }

        $this->isProcessingImport = true;

        try {
            $filePath = $this->uploadFile->getRealPath();
            
            if ($this->uploadType === 'nohp') {
                $importer = new SiswaUpdateNoHpImport();
            } else {
                $importer = new SiswaUpdateDataImport();
            }
            
            Excel::import($importer, $filePath);
            
            $summary = $importer->getSummary();
            
            $this->closeUploadModal();
            $this->resetPage(); // Refresh data

            session()->flash('success', "Proses Update Selesai! {$summary['berhasil']} data berhasil diperbarui. {$summary['skip']} data dilewati.");
        } catch (\Exception $e) {
            $this->importError = 'Gagal memproses file: ' . $e->getMessage();
        }

        $this->isProcessingImport = false;
    }


    public function openEditDataModal($studentId)
    {
        $student = Siswa::find($studentId);
        if ($student) {
            $this->editDataStudentId = $student->id;
            $this->editDataNisn = $student->nisn ?? '';
            $this->editDataNis = $student->nis ?? '';
            $this->editDataName = $student->name ?? '';
            $this->editDataBirthPlace = $student->birth_place ?? '';
            $this->editDataBirthDate = $student->birth_date ? $student->birth_date->format('Y-m-d') : '';
            $this->editDataAddress = $student->address ?? '';
            $this->editDataNoHp = $student->no_hp ?? '';
            
            $this->showEditDataModal = true;
            $this->dispatch('modal-open');
        }
    }

    public function closeEditDataModal()
    {
        $this->showEditDataModal = false;
        $this->editDataStudentId = null;
        $this->reset(['editDataNisn', 'editDataNis', 'editDataName', 'editDataBirthPlace', 'editDataBirthDate', 'editDataAddress', 'editDataNoHp']);
        $this->dispatch('modal-close');
    }

    public function saveEditData()
    {
        $this->validate([
            'editDataNisn' => 'nullable|string|max:50',
            'editDataNis' => 'nullable|string|max:50',
            'editDataName' => 'required|string|max:255',
            'editDataBirthPlace' => 'nullable|string|max:100',
            'editDataBirthDate' => 'nullable|date',
            'editDataAddress' => 'nullable|string|max:500',
            'editDataNoHp' => 'nullable|string|max:20',
        ]);

        if ($this->editDataStudentId) {
            $student = Siswa::find($this->editDataStudentId);
            if ($student) {
                // Update User's name as well if they have an account
                if ($student->user_id) {
                    $user = \App\Models\User::find($student->user_id);
                    if ($user) {
                        $user->name = $this->editDataName;
                        $user->save();
                    }
                }

                $student->nisn = $this->editDataNisn;
                $student->nis = $this->editDataNis;
                $student->name = $this->editDataName;
                $student->birth_place = $this->editDataBirthPlace;
                $student->birth_date = $this->editDataBirthDate ?: null;
                $student->address = $this->editDataAddress;
                $student->no_hp = $this->editDataNoHp;
                
                $student->save();
                session()->flash('success', 'Data siswa berhasil diperbarui.');
            }
        }
        $this->closeEditDataModal();
    }

    public function openEditNoHpModal($studentId)
    {
        $student = Siswa::find($studentId);
        if ($student) {
            $this->editNoHpStudentId = $student->id;
            $this->editNoHpSiswa = $student->no_hp ?? '';
            $this->editNoHpOrtu = $student->no_hp_orang_tua ?? '';
            
            $this->showEditNoHpModal = true;
            $this->dispatch('modal-open');
        }
    }

    public function closeEditNoHpModal()
    {
        $this->showEditNoHpModal = false;
        $this->editNoHpStudentId = null;
        $this->reset(['editNoHpSiswa', 'editNoHpOrtu']);
        $this->dispatch('modal-close');
    }

    public function saveEditNoHp()
    {
        $this->validate([
            'editNoHpSiswa' => 'nullable|string|max:20',
            'editNoHpOrtu' => 'nullable|string|max:20',
        ]);

        if ($this->editNoHpStudentId) {
            $student = Siswa::find($this->editNoHpStudentId);
            if ($student) {
                // Validasi/bersihkan format seperti pada Import No HP
                $hpSiswa = $this->editNoHpSiswa;
                $hpOrtu = $this->editNoHpOrtu;

                if (!empty($hpSiswa)) {
                    $hpSiswa = preg_replace('/[^0-9]/', '', $hpSiswa);
                    if (str_starts_with($hpSiswa, '0')) $hpSiswa = '62' . substr($hpSiswa, 1);
                }
                if (!empty($hpOrtu)) {
                    $hpOrtu = preg_replace('/[^0-9]/', '', $hpOrtu);
                    if (str_starts_with($hpOrtu, '0')) $hpOrtu = '62' . substr($hpOrtu, 1);
                }

                $student->no_hp = $hpSiswa ?: null;
                $student->no_hp_orang_tua = $hpOrtu ?: null;
                $student->save();

                session()->flash('success', 'Nomor HP Siswa & Orang Tua berhasil diperbarui.');
            }
        }
        $this->closeEditNoHpModal();
    }

    public function openChangePasswordModal($studentId)
    {
        $student = Siswa::find($studentId);
        if ($student) {
            if (!$student->user_id) {
                session()->flash('error', 'Siswa ini belum memiliki akun / belum terhubung dengan akun login.');
                return;
            }
            $this->changePasswordStudentId = $student->id;
            $this->newPassword = '';
            $this->newPasswordConfirmation = '';
            $this->showChangePasswordModal = true;
            $this->dispatch('modal-open');
        }
    }

    public function closeChangePasswordModal()
    {
        $this->showChangePasswordModal = false;
        $this->changePasswordStudentId = null;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->dispatch('modal-close');
    }

    public function saveNewPassword()
    {
        $this->validate([
            'newPassword' => 'required|string|min:6|same:newPasswordConfirmation',
            'newPasswordConfirmation' => 'required|string',
        ], [
            'newPassword.same' => 'Konfirmasi password tidak cocok.',
            'newPassword.min' => 'Password minimal 6 karakter.'
        ]);

        if ($this->changePasswordStudentId) {
            $student = Siswa::find($this->changePasswordStudentId);
            if ($student && $student->user_id) {
                $user = \App\Models\User::find($student->user_id);
                if ($user) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($this->newPassword);
                    // Opsi tambahan: $user->must_change_password = true; jika ingin memaksa mereka mengganti saat login pertama kali
                    $user->save();
                    session()->flash('success', 'Password akun siswa berhasil diubah.');
                }
            }
        }
        $this->closeChangePasswordModal();
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-guru.data-siswa-list', [
            'students' => $this->students
        ])->title('Data Siswa - Portal Guru');
    }
}
