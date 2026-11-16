<?php

namespace App\Livewire\PortalPresensi;

use App\Filament\Presensi\Resources\HariLiburs\Pages\ListHariLiburs;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Services\KalenderSekolahService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.portal')]
class PengaturanPresensi extends Component
{
    use WithPagination;

    public string $activeTab = 'presensi'; // 'presensi' | 'hari_libur'

    // ── Tab 1: Pengaturan Presensi ────────────────────────────────────────
    public string $checkin_time = '07:00';
    public int $late_threshold_minutes = 15;
    public string $batas_scan_datang_time = '09:00';
    public bool $enable_checkout = true;
    public string $start_scan_out_time = '13:00';
    public bool $enable_sholat_dhuhur = true;
    public bool $enable_sholat_jumat = false;
    public bool $enable_presensi_kbm = true;
    public string $barcode_scan_mode = 'nisn';
    public bool $auto_alpa_active = false;
    public string $auto_alpa_time = '09:00';

    // ── Tab 2: Hari Kerja Sekolah ─────────────────────────────────────────
    public string $work_days_type = '5_hari';
    public string $effective_date = '';
    public array $work_days_history = [];

    // ── Tab 2: Filter & Modal Hari Libur ──────────────────────────────────
    public string $holidaySearch = '';
    public string $holidayTypeFilter = 'all';

    public bool $showHolidayModal = false;
    public ?string $editingHolidayId = null;
    public string $holiday_start_date = '';
    public ?string $holiday_end_date = null;
    public string $holiday_description = '';
    public string $holiday_type = 'nasional';
    public ?string $holiday_class_id = null;

    // ── Konfirmasi Aksi (Hapus / Force Libur / Impor) ─────────────────────
    public bool $showConfirmModal = false;
    public string $confirmActionType = ''; // 'delete_holiday' | 'force_libur' | 'import_nasional'
    public ?string $confirmTargetId = null;
    public string $confirmTitle = '';
    public string $confirmMessage = '';

    public function mount(): void
    {
        if (request()->query('tab') === 'hari_libur') {
            $this->activeTab = 'hari_libur';
        }

        $this->loadSchoolSettings();
    }

    protected function loadSchoolSettings(): void
    {
        $settings = PengaturanSekolah::current();

        $this->checkin_time           = $this->formatTimeForInput($settings?->checkin_time, '07:00');
        $this->late_threshold_minutes = (int) ($settings?->late_threshold_minutes ?? 15);
        $this->batas_scan_datang_time = $this->formatTimeForInput($settings?->batas_scan_datang_time, '09:00');
        $this->enable_checkout        = (bool) ($settings?->enable_checkout ?? true);
        $this->start_scan_out_time    = $this->formatTimeForInput($settings?->start_scan_out_time, '13:00');
        $this->enable_sholat_dhuhur   = (bool) ($settings?->enable_sholat_dhuhur ?? true);
        $this->enable_sholat_jumat    = (bool) ($settings?->enable_sholat_jumat ?? false);
        $this->enable_presensi_kbm    = (bool) ($settings?->enable_presensi_kbm ?? true);
        $this->barcode_scan_mode      = $settings?->barcode_scan_mode ?: 'nisn';
        $this->auto_alpa_active       = (bool) ($settings?->auto_alpa_active ?? false);
        $this->auto_alpa_time         = $this->formatTimeForInput($settings?->auto_alpa_time, '09:00');

        $this->work_days_type    = $settings?->work_days_type ?: '5_hari';
        $this->effective_date    = now('Asia/Jakarta')->toDateString();
        $this->work_days_history = is_array($settings?->work_days_history) ? $settings->work_days_history : [];
    }

    protected function formatTimeForInput(?string $time, string $fallback): string
    {
        if (empty($time)) {
            return $fallback;
        }

        return substr($time, 0, 5);
    }

    protected function formatTimeForDb(?string $time): ?string
    {
        if (empty($time)) {
            return null;
        }

        return strlen($time) === 5 ? $time . ':00' : $time;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['presensi', 'hari_libur'], true) ? $tab : 'presensi';
    }

    public function updatedHolidaySearch(): void
    {
        $this->resetPage('holidaysPage');
    }

    public function updatedHolidayTypeFilter(): void
    {
        $this->resetPage('holidaysPage');
    }

    public function updatedHolidayType($value): void
    {
        if ($value !== 'khusus') {
            $this->holiday_class_id = null;
        }
    }

    // ── Simpan Tab 1: Pengaturan Presensi ─────────────────────────────────

    public function savePresensiSettings(): void
    {
        $rules = [
            'checkin_time'           => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'late_threshold_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'batas_scan_datang_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'enable_checkout'        => ['boolean'],
            'enable_sholat_dhuhur'   => ['boolean'],
            'enable_sholat_jumat'    => ['boolean'],
            'enable_presensi_kbm'    => ['boolean'],
            'barcode_scan_mode'      => ['required', 'in:nisn,nis'],
            'auto_alpa_active'       => ['boolean'],
        ];

        if ($this->enable_checkout) {
            $rules['start_scan_out_time'] = ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'];
        }

        if ($this->auto_alpa_active) {
            $rules['auto_alpa_time'] = ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'];
        }

        $this->validate($rules, [
            'checkin_time.required'           => 'Jam masuk wajib diisi.',
            'late_threshold_minutes.required' => 'Batas toleransi terlambat wajib diisi.',
            'batas_scan_datang_time.required' => 'Batas jam absen datang wajib diisi.',
            'start_scan_out_time.required'    => 'Jam mulai absen pulang wajib diisi saat check-out aktif.',
            'auto_alpa_time.required'         => 'Jam proses otomatisasi alpa wajib diisi saat otomatisasi aktif.',
        ]);

        $payload = [
            'checkin_time'           => $this->formatTimeForDb($this->checkin_time),
            'late_threshold_minutes' => $this->late_threshold_minutes,
            'batas_scan_datang_time' => $this->formatTimeForDb($this->batas_scan_datang_time),
            'enable_checkout'        => $this->enable_checkout,
            'start_scan_out_time'    => $this->enable_checkout
                ? $this->formatTimeForDb($this->start_scan_out_time)
                : $this->formatTimeForDb('13:00'),
            'enable_sholat_dhuhur'   => $this->enable_sholat_dhuhur,
            'enable_sholat_jumat'    => $this->enable_sholat_dhuhur ? $this->enable_sholat_jumat : false,
            'enable_presensi_kbm'    => $this->enable_presensi_kbm,
            'barcode_scan_mode'      => $this->barcode_scan_mode,
            'auto_alpa_active'       => $this->auto_alpa_active,
            'auto_alpa_time'         => $this->formatTimeForDb($this->auto_alpa_time ?: '09:00'),
        ];

        $settings = PengaturanSekolah::first();
        if ($settings) {
            $settings->update($payload);
        } else {
            $settings = PengaturanSekolah::create($payload);
        }

        Cache::forget('public_pengaturan_sekolah');

        activity()
            ->performedOn($settings)
            ->causedBy(auth()->user())
            ->withProperties($payload)
            ->log('Mengubah pengaturan presensi');

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Pengaturan presensi berhasil disimpan.',
        ]);
    }

    // ── Simpan Tab 2A: Hari Kerja Sekolah ─────────────────────────────────

    public function saveWorkDaysSettings(): void
    {
        $this->validate([
            'work_days_type' => ['required', 'in:5_hari,6_hari'],
            'effective_date' => ['required', 'date'],
        ]);

        $settings = PengaturanSekolah::current();
        if (! $settings) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Data pengaturan sekolah tidak ditemukan.',
            ]);
            return;
        }

        $oldType = $settings->work_days_type ?? '5_hari';
        $history = is_array($settings->work_days_history) ? $settings->work_days_history : [];

        usort($history, fn ($a, $b) => strcmp($a['start_date'], $b['start_date']));

        $newEffectiveDate = Carbon::parse($this->effective_date)->toDateString();
        $currentTypeOnDate = app(KalenderSekolahService::class)
            ->getWorkDaysTypeForDate(Carbon::parse($this->effective_date));

        if (empty($history)) {
            $history = [
                [
                    'start_date'     => '2000-01-01',
                    'end_date'       => null,
                    'work_days_type' => $oldType,
                ],
            ];
        }

        if ($currentTypeOnDate !== $this->work_days_type) {
            $filteredHistory = array_filter($history, fn ($entry) => $entry['start_date'] < $newEffectiveDate);

            if (count($filteredHistory) > 0) {
                $lastEntryKey = array_key_last($filteredHistory);
                $filteredHistory[$lastEntryKey]['end_date'] = Carbon::parse($newEffectiveDate)
                    ->subDay()
                    ->toDateString();
            }

            $filteredHistory[] = [
                'start_date'     => $newEffectiveDate,
                'end_date'       => null,
                'work_days_type' => $this->work_days_type,
            ];

            usort($filteredHistory, fn ($a, $b) => strcmp($a['start_date'], $b['start_date']));

            $settings->update([
                'work_days_type'    => $this->work_days_type,
                'work_days_history' => array_values($filteredHistory),
            ]);
        } else {
            $settings->update([
                'work_days_type' => $this->work_days_type,
            ]);
        }

        Cache::forget('public_pengaturan_sekolah');

        activity()
            ->performedOn($settings)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_work_days_type' => $oldType,
                'new_work_days_type' => $this->work_days_type,
                'effective_date'     => $this->effective_date,
                'history'            => $settings->fresh()->work_days_history,
            ])
            ->log('Mengubah pengaturan hari kerja sekolah');

        $this->work_days_history = $settings->fresh()->work_days_history ?? [];

        $formattedDate = Carbon::parse($this->effective_date)->locale('id')->translatedFormat('d M Y');
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Pengaturan hari kerja berhasil disimpan (berlaku mulai {$formattedDate}).",
        ]);
    }

    // ── CRUD Hari Libur Modal ─────────────────────────────────────────────

    public function openCreateHolidayModal(?string $date = null): void
    {
        $this->resetValidation();
        $this->editingHolidayId    = null;
        $this->holiday_start_date  = $date ?: now('Asia/Jakarta')->toDateString();
        $this->holiday_end_date    = null;
        $this->holiday_description = '';
        $this->holiday_type        = 'nasional';
        $this->holiday_class_id    = null;
        $this->showHolidayModal    = true;
    }

    public function openEditHolidayModal(string $id): void
    {
        $this->resetValidation();
        $record = HariLibur::find($id);
        if (! $record) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Data hari libur tidak ditemukan.',
            ]);
            return;
        }

        $this->editingHolidayId    = $record->id;
        $this->holiday_start_date  = $record->start_date ? $record->start_date->toDateString() : '';
        $this->holiday_end_date    = $record->end_date ? $record->end_date->toDateString() : null;
        $this->holiday_description = $record->description ?? '';
        $this->holiday_type        = $record->type ?: 'nasional';
        $this->holiday_class_id    = $record->class_id;
        $this->showHolidayModal    = true;
    }

    public function closeHolidayModal(): void
    {
        $this->showHolidayModal = false;
        $this->resetValidation();
    }

    public function saveHoliday(): void
    {
        $rules = [
            'holiday_start_date'  => ['required', 'date'],
            'holiday_end_date'    => ['nullable', 'date', 'after_or_equal:holiday_start_date'],
            'holiday_description' => ['required', 'string', 'max:255'],
            'holiday_type'        => ['required', 'in:nasional,cuti_bersama,khusus'],
            'holiday_class_id'    => $this->holiday_type === 'khusus'
                ? ['required', 'exists:classes,id']
                : ['nullable'],
        ];

        $this->validate($rules, [
            'holiday_start_date.required'      => 'Tanggal mulai wajib diisi.',
            'holiday_end_date.after_or_equal'  => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'holiday_description.required'     => 'Keterangan / nama hari libur wajib diisi.',
            'holiday_class_id.required'        => 'Kelas wajib dipilih untuk tipe libur Khusus Kelas.',
        ]);

        $payload = [
            'start_date'  => $this->holiday_start_date,
            'end_date'    => ! empty($this->holiday_end_date) ? $this->holiday_end_date : null,
            'description' => trim($this->holiday_description),
            'type'        => $this->holiday_type,
            'class_id'    => $this->holiday_type === 'khusus' ? $this->holiday_class_id : null,
        ];

        if ($this->editingHolidayId) {
            $record = HariLibur::findOrFail($this->editingHolidayId);
            $record->update($payload);
            $msg = 'Data hari libur berhasil diperbarui.';
        } else {
            HariLibur::create($payload);
            $msg = 'Hari libur baru berhasil ditambahkan.';
        }

        $this->showHolidayModal = false;
        $this->dispatchCalendarUpdate();
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => $msg,
        ]);
    }

    // ── Konfirmasi Hapus / Force Libur / Impor ────────────────────────────

    public function confirmDeleteHoliday(string $id): void
    {
        $record = HariLibur::find($id);
        if (! $record) {
            return;
        }

        $this->confirmActionType = 'delete_holiday';
        $this->confirmTargetId   = $id;
        $this->confirmTitle      = 'Hapus Hari Libur';
        $this->confirmMessage    = "Apakah Anda yakin ingin menghapus hari libur \"{$record->description}\"? Tanggal tersebut akan kembali dihitung sebagai hari sekolah efektif.";
        $this->showConfirmModal  = true;
    }

    public function confirmForceLibur(string $id): void
    {
        $record = HariLibur::find($id);
        if (! $record) {
            return;
        }

        $rangeText = $record->end_date
            ? $record->start_date->format('d M Y') . ' s/d ' . $record->end_date->format('d M Y')
            : $record->start_date->format('d M Y');

        $this->confirmActionType = 'force_libur';
        $this->confirmTargetId   = $id;
        $this->confirmTitle      = 'Paksa Libur (Hapus Presensi)';
        $this->confirmMessage    = "Tindakan ini akan MENGHAPUS secara permanen semua data presensi (Hadir, Izin, Sakit, Alpa) pada rentang {$rangeText} untuk \"{$record->description}\". Lanjutkan?";
        $this->showConfirmModal  = true;
    }

    public function confirmImportNasional(): void
    {
        $this->confirmActionType = 'import_nasional';
        $this->confirmTargetId   = null;
        $this->confirmTitle      = 'Impor Hari Libur Nasional 2026';
        $this->confirmMessage    = 'Sistem akan menambahkan hari libur nasional & cuti bersama resmi tahun 2026 ke kalender. Data yang sudah ada tidak akan diduplikasi.';
        $this->showConfirmModal  = true;
    }

    public function closeConfirmModal(): void
    {
        $this->showConfirmModal  = false;
        $this->confirmActionType = '';
        $this->confirmTargetId   = null;
    }

    public function executeConfirmedAction(): void
    {
        match ($this->confirmActionType) {
            'delete_holiday'  => $this->executeDeleteHoliday(),
            'force_libur'     => $this->executeForceLibur(),
            'import_nasional' => $this->executeImportNasional(),
            default           => null,
        };

        $this->closeConfirmModal();
    }

    protected function executeDeleteHoliday(): void
    {
        $record = HariLibur::find($this->confirmTargetId);
        if (! $record) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Data hari libur tidak ditemukan.']);
            return;
        }

        activity()
            ->performedOn($record)
            ->causedBy(auth()->user())
            ->withProperties([
                'description' => $record->description,
                'start_date'  => $record->start_date ? $record->start_date->toDateString() : null,
                'end_date'    => $record->end_date ? $record->end_date->toDateString() : null,
                'type'        => $record->type,
            ])
            ->log('Menghapus hari libur');

        $record->delete();

        $this->dispatchCalendarUpdate();
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Hari libur berhasil dihapus.',
        ]);
    }

    protected function executeForceLibur(): void
    {
        $record = HariLibur::find($this->confirmTargetId);
        if (! $record) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Data hari libur tidak ditemukan.']);
            return;
        }

        $query = Presensi::where('date', '>=', $record->start_date)
            ->where('date', '<=', $record->end_date ?: $record->start_date);

        if ($record->class_id) {
            $query->where('class_id', $record->class_id);
        }

        $deleted = $query->delete();

        activity()
            ->performedOn($record)
            ->causedBy(auth()->user())
            ->withProperties([
                'description'      => $record->description,
                'start_date'       => $record->start_date ? $record->start_date->toDateString() : null,
                'end_date'         => $record->end_date ? $record->end_date->toDateString() : null,
                'deleted_presensi' => $deleted,
            ])
            ->log('Menjalankan Force Libur');

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Force Libur berhasil: {$deleted} data presensi telah dihapus.",
        ]);
    }

    protected function executeImportNasional(): void
    {
        $holidays = ListHariLiburs::getHariLiburNasional2026();
        $imported = 0;
        $skipped  = 0;

        foreach ($holidays as $item) {
            $exists = HariLibur::where('start_date', $item['date'])
                ->where('description', $item['desc'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            HariLibur::create([
                'start_date'  => $item['date'],
                'end_date'    => null,
                'description' => $item['desc'],
                'type'        => $item['type'],
                'class_id'    => null,
            ]);
            $imported++;
        }

        if ($imported > 0) {
            $this->dispatchCalendarUpdate();
            $extra = $skipped > 0 ? " ({$skipped} data yang sudah ada dilewati)" : '';
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => "Berhasil mengimpor {$imported} hari libur nasional 2026{$extra}.",
            ]);
        } else {
            $this->dispatch('notify', [
                'type'    => 'info',
                'message' => 'Semua hari libur nasional 2026 sudah ada.',
            ]);
        }
    }

    public function getCalendarEvents(): array
    {
        return HariLibur::with('kelas:id,name')->get()->map(function (HariLibur $holiday) {
            $color = match ($holiday->type) {
                'nasional'     => '#3b82f6',
                'cuti_bersama' => '#10b981',
                'khusus'       => '#f59e0b',
                default        => '#6b7280',
            };

            $title = $holiday->description;
            if ($holiday->type === 'khusus' && $holiday->kelas) {
                $title .= ' (' . $holiday->kelas->name . ')';
            }

            return [
                'id'    => $holiday->id,
                'title' => $title,
                'start' => $holiday->start_date->toDateString(),
                'end'   => $holiday->end_date
                    ? $holiday->end_date->copy()->addDay()->toDateString()
                    : $holiday->start_date->copy()->addDay()->toDateString(),
                'color' => $color,
            ];
        })->toArray();
    }

    protected function dispatchCalendarUpdate(): void
    {
        $this->dispatch('holiday-calendar-updated', events: $this->getCalendarEvents());
    }

    public function render()
    {
        $holidaysQuery = HariLibur::with('kelas:id,name')
            ->orderBy('start_date', 'desc');

        if ($this->holidayTypeFilter !== 'all') {
            $holidaysQuery->where('type', $this->holidayTypeFilter);
        }

        if (trim($this->holidaySearch) !== '') {
            $term = '%' . trim($this->holidaySearch) . '%';
            $holidaysQuery->where('description', 'like', $term);
        }

        $holidays = $holidaysQuery->paginate(10, ['*'], 'holidaysPage');

        $classes = Kelas::orderBy('name', 'asc')->get(['id', 'name']);

        $holidayLogs = Activity::where(function ($query) {
            $query->where('subject_type', HariLibur::class)
                ->orWhere('subject_type', PengaturanSekolah::class);
        })
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('livewire.portal-presensi.pengaturan-presensi', [
            'holidays'       => $holidays,
            'classes'        => $classes,
            'holidayLogs'    => $holidayLogs,
            'calendarEvents' => $this->activeTab === 'hari_libur' ? $this->getCalendarEvents() : [],
        ])->title('Pengaturan Presensi & Hari Libur - Portal Presensi');
    }
}

