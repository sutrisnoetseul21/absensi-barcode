<?php

namespace App\Livewire\PortalSetup;

use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class SetupDasar extends Component
{
    use WithFileUploads;

    #[Url(as: 'tab')]
    public string $activeTab = 'sekolah';

    // Tab 1: Identitas & Jenjang Sekolah
    public string $school_level = 'SMP';
    public string $school_status = 'negeri';
    public string $school_name = '';
    public string $school_address = '';
    public string $kota = '';
    public string $school_email = '';
    public string $school_phone = '';
    public string $principal_name = '';
    public string $principal_nip = '';
    public string $active_semester = 'ganjil';
    public ?string $existing_logo_path = null;
    public $logo_upload = null;

    // Tab 2: Tahun Ajaran
    public int $start_year;
    public int $end_year;
    public bool $set_active = true;

    // Tab 3: Master Kelas (Satuan)
    public ?int $class_grade_level = null;
    public string $class_major = '';
    public string $class_name = '';

    // Tab 3: Generate Massal Kelas
    public ?int $bulk_grade_level = null;
    public string $bulk_format = 'angka'; // 'angka' atau 'romawi'
    public string $bulk_major = '';
    public string $bulk_pattern = 'huruf'; // 'huruf' atau 'angka'
    public int $bulk_count = 3;

    // Edit Kelas Inline
    public ?string $editingClassId = null;
    public string $edit_class_name = '';
    public ?int $edit_class_grade_level = null;
    public string $edit_class_major = '';

    // Mode Terkunci & Verifikasi Password Super Admin (Opsi B)
    public bool $isUnlocked = false;
    public bool $showUnlockModal = false;
    public string $unlockPassword = '';

    public function mount(): void
    {
        if (!in_array($this->activeTab, ['sekolah', 'tahun-ajaran', 'kelas'], true)) {
            $this->activeTab = 'sekolah';
        }

        if (!$this->isSchoolConfigured() && $this->activeTab !== 'sekolah') {
            $this->activeTab = 'sekolah';
        }

        $this->loadSchoolData();

        $currentYear = (int) now('Asia/Jakarta')->format('Y');
        $this->start_year = $currentYear;
        $this->end_year = $currentYear + 1;

        $this->syncDefaultGradeLevels();
    }

    protected function loadSchoolData(): void
    {
        $school = PengaturanSekolah::current();
        if ($school) {
            $this->school_level = $school->school_level ?: 'SMP';
            $this->school_status = $school->school_status ?: 'negeri';
            $this->school_name = $school->school_name ?? '';
            $this->school_address = $school->school_address ?? '';
            $this->kota = $school->kota ?? '';
            $this->school_email = $school->school_email ?? '';
            $this->school_phone = $school->school_phone ?? '';
            $this->principal_name = $school->principal_name ?? '';
            $this->principal_nip = $school->principal_nip ?? '';
            $this->active_semester = $school->active_semester ?: 'ganjil';
            $this->existing_logo_path = $school->school_logo_path;
        }
    }

    protected function syncDefaultGradeLevels(): void
    {
        $options = $this->getGradeOptions();
        $firstGrade = array_key_first($options);

        if (!$this->class_grade_level || !array_key_exists($this->class_grade_level, $options)) {
            $this->class_grade_level = $firstGrade;
        }
        if (!$this->bulk_grade_level || !array_key_exists($this->bulk_grade_level, $options)) {
            $this->bulk_grade_level = $firstGrade;
        }
        if (in_array($this->school_level, ['SMA', 'SMK'], true)) {
            $this->bulk_format = 'romawi';
            if ($this->school_level === 'SMK') {
                $this->bulk_pattern = 'angka';
            }
        } else {
            $this->bulk_format = 'angka';
            $this->bulk_pattern = 'huruf';
        }
    }

    public function updatedSchoolLevel(): void
    {
        $this->syncDefaultGradeLevels();
    }

    public function switchTab(string $tab): void
    {
        if (!in_array($tab, ['sekolah', 'tahun-ajaran', 'kelas'], true)) {
            return;
        }

        if ($tab !== 'sekolah' && !$this->isSchoolConfigured()) {
            $this->activeTab = 'sekolah';
            session()->flash('setup_error', 'Simpan Identitas & Jenjang Sekolah terlebih dahulu sebelum membuka Tahun Ajaran atau Master Kelas.');
            return;
        }

        $this->activeTab = $tab;
    }

    public function updatedStartYear($value): void
    {
        $year = (int) $value;
        if ($year >= 2000 && $year <= 2100) {
            $this->end_year = $year + 1;
        }
    }

    public function getGradeOptions(): array
    {
        return match ($this->school_level) {
            'SD' => [
                1 => 'Kelas 1 (SD)',
                2 => 'Kelas 2 (SD)',
                3 => 'Kelas 3 (SD)',
                4 => 'Kelas 4 (SD)',
                5 => 'Kelas 5 (SD)',
                6 => 'Kelas 6 (SD)',
            ],
            'SMP' => [
                7 => 'Kelas 7 (SMP)',
                8 => 'Kelas 8 (SMP)',
                9 => 'Kelas 9 (SMP)',
            ],
            'SMA' => [
                10 => 'Kelas 10 / X (SMA)',
                11 => 'Kelas 11 / XI (SMA)',
                12 => 'Kelas 12 / XII (SMA)',
            ],
            'SMK' => [
                10 => 'Kelas 10 / X (SMK)',
                11 => 'Kelas 11 / XI (SMK)',
                12 => 'Kelas 12 / XII (SMK)',
                13 => 'Kelas 13 / XIII (SMK 4 Tahun)',
            ],
            default => [
                7 => 'Kelas 7',
                8 => 'Kelas 8',
                9 => 'Kelas 9',
            ],
        };
    }

    // ==================== KEAMANAN: BUKA KUNCI SUPER ADMIN (OPSI B) ====================

    public function openUnlockModal(): void
    {
        $this->resetValidation('unlockPassword');
        $this->unlockPassword = '';
        $this->showUnlockModal = true;
    }

    public function closeUnlockModal(): void
    {
        $this->showUnlockModal = false;
        $this->unlockPassword = '';
        $this->resetValidation('unlockPassword');
    }

    public function verifyUnlockPassword(): void
    {
        $this->validate([
            'unlockPassword' => 'required|string',
        ], [
            'unlockPassword.required' => 'Password Super Admin wajib diisi.',
        ]);

        $superAdmins = User::where('is_super_admin', true)
            ->orWhereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->get();

        $verified = $superAdmins->contains(fn (User $admin) => Hash::check($this->unlockPassword, $admin->password));

        if (!$verified) {
            $this->addError('unlockPassword', 'Password Super Admin tidak valid.');
            return;
        }

        $this->isUnlocked = true;
        $this->closeUnlockModal();
        session()->flash('setup_success', 'Kunci edit berhasil dibuka. Jangan lupa kunci kembali setelah selesai.');
    }

    public function lockEdit(): void
    {
        $this->isUnlocked = false;
        $this->cancelEditClass();
        session()->flash('setup_success', 'Mode aman aktif. Pengaturan kembali dikunci.');
    }

    protected function isSchoolConfigured(): bool
    {
        $school = PengaturanSekolah::current();
        return $school && filled($school->school_name) && filled($school->school_address);
    }

    protected function ensureUnlockedIfRequired(bool $requiresUnlock): bool
    {
        if ($requiresUnlock && !$this->isUnlocked) {
            $this->openUnlockModal();
            session()->flash('setup_error', 'Data sudah terkunci. Masukkan password Super Admin untuk melakukan perubahan.');
            return false;
        }

        return true;
    }

    // ==================== TAB 1: SEKOLAH ====================

    public function saveSchool(): void
    {
        if (!$this->ensureUnlockedIfRequired($this->isSchoolConfigured())) {
            return;
        }

        $this->validate([
            'school_level'   => 'required|in:SD,SMP,SMA,SMK',
            'school_status'  => 'required|in:negeri,swasta',
            'school_name'    => 'required|string|max:255',
            'school_address' => 'required|string|max:500',
            'kota'           => 'nullable|string|max:100',
            'school_email'   => 'nullable|email|max:255',
            'school_phone'   => 'nullable|string|max:50',
            'principal_name' => 'nullable|string|max:255',
            'principal_nip'  => 'nullable|string|max:50',
            'logo_upload'    => 'nullable|image|max:2048',
        ], [
            'school_name.required'    => 'Nama sekolah wajib diisi.',
            'school_address.required' => 'Alamat sekolah wajib diisi.',
            'logo_upload.image'       => 'File logo harus berupa gambar (PNG/JPG/WebP).',
            'logo_upload.max'         => 'Ukuran logo maksimal 2 MB.',
        ]);

        $school = PengaturanSekolah::current() ?? new PengaturanSekolah();

        if ($this->logo_upload) {
            if ($school->school_logo_path && Storage::disk('public')->exists($school->school_logo_path)) {
                Storage::disk('public')->delete($school->school_logo_path);
            }
            $school->school_logo_path = $this->logo_upload->store('school-assets', 'public');
            $this->existing_logo_path = $school->school_logo_path;
            $this->logo_upload = null;
        }

        $school->fill([
            'school_level'    => $this->school_level,
            'school_status'   => $this->school_status,
            'school_name'     => trim($this->school_name),
            'school_address'  => trim($this->school_address),
            'kota'            => trim($this->kota) ?: null,
            'school_email'    => trim($this->school_email) ?: null,
            'school_phone'    => trim($this->school_phone) ?: null,
            'principal_name'  => trim($this->principal_name) ?: null,
            'principal_nip'   => trim($this->principal_nip) ?: null,
        ]);
        $school->save();

        Cache::forget('public_pengaturan_sekolah');
        $this->syncDefaultGradeLevels();
        $this->isUnlocked = false;

        session()->flash('setup_success', 'Identitas & jenjang sekolah berhasil disimpan dan dikunci kembali.');
    }

    // ==================== TAB 2: TAHUN AJARAN ====================

    public function saveActiveSemester(): void
    {
        $hasActiveYear = TahunAjaran::where('status', 'aktif')->exists();
        if (!$this->ensureUnlockedIfRequired($hasActiveYear)) {
            return;
        }

        $this->validate([
            'active_semester' => 'required|in:ganjil,genap',
        ]);

        $school = PengaturanSekolah::current() ?? new PengaturanSekolah();
        $school->active_semester = $this->active_semester;
        $school->save();

        Cache::forget('public_pengaturan_sekolah');
        session()->flash('setup_success', 'Semester aktif berhasil diubah menjadi Semester ' . ucfirst($this->active_semester) . '.');
    }

    public function createTahunAjaran(): void
    {
        if (!$this->ensureUnlockedIfRequired(TahunAjaran::exists())) {
            return;
        }

        $this->validate([
            'start_year' => 'required|integer|min:2000|max:2099',
            'end_year'   => 'required|integer|gte:start_year|max:2100',
        ], [
            'start_year.required' => 'Tahun mulai wajib diisi.',
            'end_year.gte'        => 'Tahun selesai tidak boleh lebih kecil dari tahun mulai.',
        ]);

        $exists = TahunAjaran::where('start_year', $this->start_year)
            ->where('end_year', $this->end_year)
            ->exists();

        if ($exists) {
            $this->addError('start_year', "Tahun Ajaran {$this->start_year}/{$this->end_year} sudah terdaftar.");
            return;
        }

        TahunAjaran::create([
            'start_year' => $this->start_year,
            'end_year'   => $this->end_year,
            'status'     => $this->set_active ? 'aktif' : 'arsip',
        ]);

        session()->flash('setup_success', "Tahun Ajaran {$this->start_year}/{$this->end_year} berhasil ditambahkan.");
    }

    public function activateTahunAjaran(string $id): void
    {
        $hasActiveYear = TahunAjaran::where('status', 'aktif')->exists();
        if (!$this->ensureUnlockedIfRequired($hasActiveYear)) {
            return;
        }

        $year = TahunAjaran::findOrFail($id);
        $year->status = 'aktif';
        $year->save();

        Cache::forget('public_pengaturan_sekolah');
        session()->flash('setup_success', "Tahun Ajaran {$year->name} sekarang aktif.");
    }

    public function deleteTahunAjaran(string $id): void
    {
        if (!$this->ensureUnlockedIfRequired(true)) {
            return;
        }

        $year = TahunAjaran::withCount(['kelasAjarans', 'enrollments', 'absensis'])->findOrFail($id);

        if ($year->status === 'aktif') {
            session()->flash('setup_error', 'Tahun Ajaran yang sedang aktif tidak boleh dihapus.');
            return;
        }

        if ($year->kelas_ajarans_count > 0 || $year->enrollments_count > 0 || $year->absensis_count > 0) {
            session()->flash('setup_error', "Tahun Ajaran {$year->name} sudah memiliki data relasi akademik/presensi sehingga tidak dapat dihapus.");
            return;
        }

        $name = $year->name;
        $year->delete();
        session()->flash('setup_success', "Tahun Ajaran {$name} berhasil dihapus.");
    }

    // ==================== TAB 3: MASTER KELAS ====================

    public function createSingleClass(): void
    {
        if (!$this->ensureUnlockedIfRequired(Kelas::exists())) {
            return;
        }

        $validGrades = implode(',', array_keys($this->getGradeOptions()));

        $this->validate([
            'class_grade_level' => "required|integer|in:{$validGrades}",
            'class_major'       => 'nullable|string|max:100',
            'class_name'        => 'required|string|max:30',
        ], [
            'class_grade_level.required' => 'Tingkat kelas wajib dipilih.',
            'class_name.required'        => 'Nama kelas wajib diisi.',
        ]);

        $cleanName = trim($this->class_name);
        if (Kelas::where('name', $cleanName)->exists()) {
            $this->addError('class_name', "Kelas \"{$cleanName}\" sudah terdaftar.");
            return;
        }

        Kelas::create([
            'grade_level' => $this->class_grade_level,
            'major'       => trim($this->class_major) ?: null,
            'name'        => $cleanName,
        ]);

        $this->class_name = '';
        session()->flash('setup_success', "Kelas \"{$cleanName}\" berhasil ditambahkan.");
    }

    public function getBulkPreviewNames(): array
    {
        if (!$this->bulk_grade_level) {
            return [];
        }

        $count = max(1, min(15, (int) $this->bulk_count));
        $prefix = $this->bulk_format === 'romawi'
            ? $this->toRoman((int) $this->bulk_grade_level)
            : (string) $this->bulk_grade_level;

        $major = trim($this->bulk_major);
        $names = [];

        for ($i = 0; $i < $count; $i++) {
            $suffix = $this->bulk_pattern === 'huruf'
                ? chr(65 + $i) // A, B, C...
                : (string) ($i + 1); // 1, 2, 3...

            if ($major !== '') {
                $names[] = "{$prefix} {$major} {$suffix}";
            } elseif ($this->bulk_format === 'romawi') {
                $names[] = "{$prefix}-{$suffix}";
            } else {
                $names[] = "{$prefix}{$suffix}";
            }
        }

        return $names;
    }

    public function generateBulkClasses(): void
    {
        if (!$this->ensureUnlockedIfRequired(Kelas::exists())) {
            return;
        }

        $validGrades = implode(',', array_keys($this->getGradeOptions()));

        $this->validate([
            'bulk_grade_level' => "required|integer|in:{$validGrades}",
            'bulk_count'       => 'required|integer|min:1|max:15',
            'bulk_major'       => 'nullable|string|max:100',
        ]);

        $names = $this->getBulkPreviewNames();
        $major = trim($this->bulk_major) ?: null;
        $created = 0;
        $skipped = 0;

        foreach ($names as $name) {
            if (Kelas::where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            Kelas::create([
                'grade_level' => $this->bulk_grade_level,
                'major'       => $major,
                'name'        => $name,
            ]);
            $created++;
        }

        $msg = "{$created} kelas berhasil dibuat.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} dilewati karena sudah ada)";
        }

        session()->flash('setup_success', $msg);
    }

    public function startEditClass(string $id): void
    {
        if (!$this->ensureUnlockedIfRequired(true)) {
            return;
        }

        $kelas = Kelas::findOrFail($id);
        $this->editingClassId = $kelas->id;
        $this->edit_class_name = $kelas->name;
        $this->edit_class_grade_level = $kelas->grade_level;
        $this->edit_class_major = $kelas->major ?? '';
    }

    public function cancelEditClass(): void
    {
        $this->editingClassId = null;
        $this->edit_class_name = '';
        $this->edit_class_grade_level = null;
        $this->edit_class_major = '';
    }

    public function saveEditClass(): void
    {
        if (!$this->editingClassId) {
            return;
        }

        $validGrades = implode(',', array_keys($this->getGradeOptions()));
        $this->validate([
            'edit_class_name'        => 'required|string|max:30',
            'edit_class_grade_level' => "required|integer|in:{$validGrades}",
            'edit_class_major'       => 'nullable|string|max:100',
        ]);

        $cleanName = trim($this->edit_class_name);
        $duplicate = Kelas::where('name', $cleanName)
            ->where('id', '!=', $this->editingClassId)
            ->exists();

        if ($duplicate) {
            $this->addError('edit_class_name', "Nama kelas \"{$cleanName}\" sudah digunakan.");
            return;
        }

        $kelas = Kelas::findOrFail($this->editingClassId);
        $kelas->update([
            'name'        => $cleanName,
            'grade_level' => $this->edit_class_grade_level,
            'major'       => trim($this->edit_class_major) ?: null,
        ]);

        $this->cancelEditClass();
        session()->flash('setup_success', "Data kelas \"{$cleanName}\" berhasil diperbarui.");
    }

    public function deleteClass(string $id): void
    {
        if (!$this->ensureUnlockedIfRequired(true)) {
            return;
        }

        $kelas = Kelas::withCount(['enrollments', 'absensis'])->findOrFail($id);

        if ($kelas->enrollments_count > 0 || $kelas->absensis_count > 0) {
            session()->flash('setup_error', "Kelas \"{$kelas->name}\" sudah memiliki data siswa/presensi sehingga tidak dapat dihapus.");
            return;
        }

        $name = $kelas->name;
        $kelas->delete();
        session()->flash('setup_success', "Kelas \"{$name}\" berhasil dihapus.");
    }

    protected function toRoman(int $number): string
    {
        return match ($number) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII', 13 => 'XIII',
            default => (string) $number,
        };
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $school = PengaturanSekolah::current();
        $years = TahunAjaran::withCount(['kelasAjarans', 'enrollments'])
            ->orderBy('start_year', 'desc')
            ->get();

        $classes = Kelas::withCount(['enrollments'])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $groupedClasses = $classes->groupBy('grade_level');

        $schoolReady = $school && filled($school->school_name) && filled($school->school_address);
        $yearReady = $years->contains('status', 'aktif');
        $classReady = $classes->isNotEmpty();

        return view('livewire.portal-setup.setup-dasar', [
            'gradeOptions'     => $this->getGradeOptions(),
            'years'            => $years,
            'classes'          => $classes,
            'groupedClasses'   => $groupedClasses,
            'bulkPreviewNames' => $this->getBulkPreviewNames(),
            'schoolReady'      => $schoolReady,
            'yearReady'        => $yearReady,
            'classReady'       => $classReady,
            'allReady'         => $schoolReady && $yearReady && $classReady,
        ])->title('Setting Sekolah, Tahun Ajaran & Kelas — Portal Setup');
    }
}
