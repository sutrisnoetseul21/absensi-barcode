# LANGKAH IMPLEMENTASI: MODUL GURU WALI — PORTAL ADMIN

> **Target**: Fitur CRUD Kelompok Guru Wali di `/admin`
> **Strategi**: Implementasi bertahap — setiap langkah diikuti tes sebelum lanjut ke langkah berikutnya.
> **Namespace Filament**: `App\Filament\Akademik\Resources` (sesuai pola SiswaResource)
> **Tanggal Mulai**: September 2026

---

## STATUS PROGRES

| # | Langkah | Status | Catatan |
| :--- | :--- | :--- | :--- |
| 1 | Migration: Buat tabel DB | `[x]` | Selesai & revisi skema `teacher_id` unique diterapkan |
| 2 | Model: KelompokGuruWali | `[x]` | Selesai (`app/Models/KelompokGuruWali.php`) |
| 3 | Model: KelompokGuruWaliSiswa | `[x]` | Selesai (`app/Models/KelompokGuruWaliSiswa.php`) |
| 3.5 | Observer: Auto-arsip siswa lulus/mutasi | `[x]` | Selesai (`HandleGuruWaliStudentGraduated/Mutated`) |
| 4 | Seeder: Data dummy kelompok | `[x]` | Selesai (`KelompokGuruWaliSeeder.php`) |
| 5 | Filament Resource: Daftar & Form Kelompok | `[x]` | Selesai + Modal Dual-Pane "Kelola Anggota" |
| 6 | Relation Manager: Daftar Siswa dalam Kelompok | `[x]` | Selesai + TernaryFilter status & badge keterangan |
| 7 | Validasi: 1 siswa hanya 1 kelompok aktif | `[x]` | Selesai (DB unique constraint + hook validasi form) |
| 8 | Daftarkan ke AdminPanelProvider | `[x]` | Selesai (Otomatis via `discoverResources`) |
| 9 | Tes UI end-to-end | `[/]` | Sebagian besar skenario telah teruji, tahap verifikasi akhir |

---

## LANGKAH 1 — Migration: Buat Tabel Database

**File yang dibuat**:
- `database/migrations/2026_09_12_XXXXXX_create_kelompok_guru_wali_tables.php`

**Isi migration** (buat satu file untuk dua tabel sekaligus):

```php
Schema::create('kelompok_guru_wali', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('nama_kelompok', 100);
    $table->foreignUuid('teacher_id')->unique('unique_teacher_kelompok')->constrained('teachers')->restrictOnDelete();
    $table->boolean('status_aktif')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('kelompok_guru_wali_siswa', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('kelompok_id')->constrained('kelompok_guru_wali')->cascadeOnDelete();
    $table->foreignUuid('student_id')->constrained('students')->restrictOnDelete();
    $table->year('tahun_masuk'); // Tahun siswa bergabung ke kelompok ini
    $table->boolean('status_aktif')->default(true);
    $table->timestamps();

    // 1 siswa aktif hanya di 1 kelompok
    $table->unique(['student_id', 'status_aktif'], 'unique_student_aktif');
});
```

### ✅ TES LANGKAH 1
```bash
php artisan migrate
```
**Ekspektasi**:
- Tidak ada error migration
- Tabel `kelompok_guru_wali` muncul di database
- Tabel `kelompok_guru_wali_siswa` muncul di database
- Cek: `php artisan migrate:status` → kedua tabel tercatat

---

## LANGKAH 2 — Model: `KelompokGuruWali`

**File yang dibuat**: `app/Models/KelompokGuruWali.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelompokGuruWali extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'kelompok_guru_wali';

    protected $fillable = [
        'nama_kelompok',
        'teacher_id',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    // Relasi ke Guru (tabel teachers)
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    // Relasi ke anggota kelompok (tabel kelompok_guru_wali_siswa)
    public function anggota(): HasMany
    {
        return $this->hasMany(KelompokGuruWaliSiswa::class, 'kelompok_id');
    }

    // Relasi ke siswa (through kelompok_guru_wali_siswa)
    public function siswa()
    {
        return $this->hasManyThrough(
            Siswa::class,
            KelompokGuruWaliSiswa::class,
            'kelompok_id',   // FK di kelompok_guru_wali_siswa
            'id',            // PK di students
            'id',            // PK di kelompok_guru_wali
            'student_id'     // FK di kelompok_guru_wali_siswa
        );
    }
}
```

### ✅ TES LANGKAH 2
```bash
php artisan tinker
```
```php
// Di Tinker:
App\Models\KelompokGuruWali::count(); // → 0 (tidak error)
App\Models\KelompokGuruWali::create([
    'nama_kelompok' => 'Test Kelompok',
    'teacher_id' => App\Models\Guru::first()->id,
    'status_aktif' => true,
]);
App\Models\KelompokGuruWali::count(); // → 1
App\Models\KelompokGuruWali::first()->guru->name; // → nama guru
```
**Ekspektasi**: Tidak ada error, relasi `guru` terbaca.

---

## LANGKAH 3 — Model: `KelompokGuruWaliSiswa`

**File yang dibuat**: `app/Models/KelompokGuruWaliSiswa.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelompokGuruWaliSiswa extends Model
{
    use HasUuids;

    protected $table = 'kelompok_guru_wali_siswa';

    protected $fillable = [
        'kelompok_id',
        'student_id',
        'tahun_masuk',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tahun_masuk'  => 'integer',
    ];

    // Relasi ke kelompok
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokGuruWali::class, 'kelompok_id');
    }

    // Relasi ke siswa
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
```

**Update `KelompokGuruWali` model** — tambahkan relasi anggota aktif:

```php
// Tambahkan di KelompokGuruWali.php
public function anggotaAktif(): HasMany
{
    return $this->hasMany(KelompokGuruWaliSiswa::class, 'kelompok_id')
        ->where('status_aktif', true);
}
```

**Update `Siswa` model** — tambahkan relasi ke kelompok:

```php
// Tambahkan di app/Models/Siswa.php
public function kelompokGuruWali(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(KelompokGuruWaliSiswa::class, 'student_id')
        ->where('status_aktif', true);
}
```

### ✅ TES LANGKAH 3
```bash
php artisan tinker
```
```php
$kelompok = App\Models\KelompokGuruWali::first();
$siswa = App\Models\Siswa::first();

App\Models\KelompokGuruWaliSiswa::create([
    'kelompok_id' => $kelompok->id,
    'student_id'  => $siswa->id,
    'status_aktif' => true,
]);

$kelompok->siswa()->count(); // → 1
$kelompok->anggotaAktif()->count(); // → 1
$siswa->kelompokGuruWali; // → KelompokGuruWaliSiswa object
```
**Ekspektasi**: Semua relasi terbaca tanpa error.

---

## LANGKAH 3.5 — Listener: Auto-Arsip Siswa saat Lulus/Mutasi

**Tujuan**: Saat siswa dinyatakan lulus atau mutasi, baris aktif siswa di `kelompok_guru_wali_siswa` otomatis di-arsipkan (`status_aktif = false`). **Jurnal (`jurnal_guru_wali`) TIDAK disentuh** — tetap tersimpan sebagai arsip riwayat pendampingan.

> **⚠️ Keputusan Arsitektur** (revisi dari rencana awal):
> Rencana awal dokumen ini menyebut `SiswaObserver` + `Siswa::observe()`. Namun setelah memeriksa codebase, project ini sudah punya **Event-Driven Architecture** untuk perubahan status siswa — tidak pernah dilakukan via `$siswa->update()` langsung, melainkan selalu melalui:
> - `GraduateStudentAction` → dispatch `StudentGraduated`
> - `MutateStudentAction` → dispatch `StudentMutated`
>
> Karena itu implementasi final menggunakan **Listener** (mengikuti pola `HandleStudentMutated` di `app/Listeners/Enrollment/`), bukan Observer Eloquent. `SiswaObserver.php` **tidak dibuat** untuk menghindari dead code.

---

**File yang dibuat**:

1. `app/Listeners/GuruWali/HandleGuruWaliStudentGraduated.php`

```php
<?php

namespace App\Listeners\GuruWali;

use App\Events\Student\StudentGraduated;
use App\Models\KelompokGuruWaliSiswa;

class HandleGuruWaliStudentGraduated
{
    public function handle(StudentGraduated $event): void
    {
        KelompokGuruWaliSiswa::where('student_id', $event->siswa->id)
            ->where('status_aktif', true)
            ->update(['status_aktif' => false]);
    }
}
```

2. `app/Listeners/GuruWali/HandleGuruWaliStudentMutated.php`

```php
<?php

namespace App\Listeners\GuruWali;

use App\Events\Student\StudentMutated;
use App\Models\KelompokGuruWaliSiswa;

class HandleGuruWaliStudentMutated
{
    public function handle(StudentMutated $event): void
    {
        KelompokGuruWaliSiswa::where('student_id', $event->siswa->id)
            ->where('status_aktif', true)
            ->update(['status_aktif' => false]);
    }
}
```

**Daftarkan kedua Listener di `app/Providers/AppServiceProvider.php`** — method `boot()`:

```php
use App\Listeners\GuruWali\HandleGuruWaliStudentGraduated;
use App\Listeners\GuruWali\HandleGuruWaliStudentMutated;

// Di dalam boot() — setelah blok StudentGraduationCancelled:
Event::listen(StudentGraduated::class, HandleGuruWaliStudentGraduated::class);
Event::listen(StudentMutated::class, HandleGuruWaliStudentMutated::class);
```

**Tambahkan relasi `riwayatGuruWali()` di `app/Models/Siswa.php`**:

```php
public function riwayatGuruWali(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\KelompokGuruWaliSiswa::class, 'student_id')
                ->orderBy('created_at', 'desc');
}
```

### ✅ TES LANGKAH 3.5 (sudah dijalankan & lulus)

Tes menggunakan `GraduateStudentAction` agar memicu Event dan Listener:

```php
// Setup: buat kelompok test + siswa aktif tanpa kelompok
$kelompok = KelompokGuruWali::create([...]);
$siswa    = Siswa::where('status', 'aktif')->whereDoesntHave('kelompokGuruWali')->first();
KelompokGuruWaliSiswa::create(['kelompok_id' => $kelompok->id, 'student_id' => $siswa->id, 'status_aktif' => true]);

// Trigger via Action (bukan ->update() langsung)
(new App\Actions\Student\GraduateStudentAction)->execute($siswa);

// Hasil terverifikasi:
$siswa->fresh()->kelompokGuruWali;                          // null ✅
$siswa->fresh()->riwayatGuruWali()->count();                // 1 (tidak hilang) ✅
$siswa->fresh()->riwayatGuruWali()->first()->status_aktif;  // false ✅
```

---

## LANGKAH 4 — Seeder: Data Dummy Kelompok

**File yang dibuat**: `database/seeders/KelompokGuruWaliSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\KelompokGuruWali;
use App\Models\KelompokGuruWaliSiswa;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class KelompokGuruWaliSeeder extends Seeder
{
    public function run(): void
    {
        $guru1 = Guru::first();
        $guru2 = Guru::skip(1)->first();

        if (!$guru1 || !$guru2) {
            $this->command->error('Dibutuhkan minimal 2 data Guru di database untuk membuat 2 kelompok dampingan berbeda. Seeder dibatalkan.');
            return;
        }

        $siswaList = Siswa::where('status', 'aktif')->take(10)->get();

        // Kelompok 1
        $kelompok1 = KelompokGuruWali::create([
            'nama_kelompok' => 'Kelompok Dandelion',
            'teacher_id'    => $guru1->id,
            'status_aktif'  => true,
        ]);

        foreach ($siswaList->take(5) as $siswa) {
            KelompokGuruWaliSiswa::create([
                'kelompok_id'  => $kelompok1->id,
                'student_id'   => $siswa->id,
                'tahun_masuk'  => 2024,
                'status_aktif' => true,
            ]);
        }

        // Kelompok 2
        $kelompok2 = KelompokGuruWali::create([
            'nama_kelompok' => 'Kelompok Mawar',
            'teacher_id'    => $guru2->id,
            'status_aktif'  => true,
        ]);

        foreach ($siswaList->skip(5)->take(5) as $siswa) {
            KelompokGuruWaliSiswa::create([
                'kelompok_id'  => $kelompok2->id,
                'student_id'   => $siswa->id,
                'tahun_masuk'  => 2024,
                'status_aktif' => true,
            ]);
        }

        $this->command->info('✅ Seeder KelompokGuruWali selesai: 2 kelompok, 10 siswa.');
    }
}
```

### ✅ TES LANGKAH 4
```bash
php artisan db:seed --class=KelompokGuruWaliSeeder
```
**Ekspektasi**:
- Muncul pesan: `✅ Seeder KelompokGuruWali selesai: 2 kelompok, 10 siswa.`
- `KelompokGuruWali::count()` → 2
- `KelompokGuruWaliSiswa::count()` → 10

---

## LANGKAH 5 — Filament Resource: Daftar & Form Kelompok

**Lokasi**: `app/Filament/Akademik/Resources/GuruWali/`

**File yang dibuat**:
```
app/Filament/Akademik/Resources/GuruWali/
├── KelompokGuruWaliResource.php        ← resource utama
└── Pages/
    ├── ListKelompokGuruWali.php        ← halaman daftar
    ├── CreateKelompokGuruWali.php      ← halaman buat baru
    └── EditKelompokGuruWali.php        ← halaman edit
```

**`KelompokGuruWaliResource.php`** (isi utama):

```php
<?php

namespace App\Filament\Akademik\Resources\GuruWali;

use App\Models\KelompokGuruWali;
use App\Models\Guru;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class KelompokGuruWaliResource extends Resource
{
    protected static ?string $model = KelompokGuruWali::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Guru Wali';
    protected static ?string $navigationLabel = 'Kelompok Dampingan';
    protected static ?string $modelLabel = 'Kelompok';
    protected static ?string $pluralModelLabel = 'Kelompok Dampingan';
    protected static ?string $slug = 'guru-wali/kelompok';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('nama_kelompok')
                ->label('Nama Kelompok')
                ->required()
                ->maxLength(100)
                ->placeholder('Contoh: Kelompok Dandelion'),

            Forms\Components\Select::make('teacher_id')
                ->label('Guru Wali')
                ->required()
                ->searchable()
                ->options(function ($record) {
                    $query = Guru::orderBy('name');
                    if ($record) {
                        $query->where(function ($q) use ($record) {
                            $q->whereDoesntHave('kelompokGuruWali')
                              ->orWhere('id', $record->teacher_id);
                        });
                    } else {
                        $query->whereDoesntHave('kelompokGuruWali');
                    }
                    return $query->pluck('name', 'id')->toArray();
                })
                ->placeholder('Pilih Guru Wali'),

            Forms\Components\Toggle::make('status_aktif')
                ->label('Kelompok Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelompok')
                    ->label('Nama Kelompok')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('guru.name')
                    ->label('Guru Wali')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('anggotaAktif_count')
                    ->label('Jumlah Siswa')
                    ->counts('anggotaAktif')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('status_aktif')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status_aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManager siswa ditambahkan di Langkah 6
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListKelompokGuruWali::route('/'),
            'create' => Pages\CreateKelompokGuruWali::route('/create'),
            'edit'   => Pages\EditKelompokGuruWali::route('/{record}/edit'),
        ];
    }
}
```

**`Pages/ListKelompokGuruWali.php`**:
```php
<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelompokGuruWali extends ListRecords
{
    protected static string $resource = KelompokGuruWaliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
```

**`Pages/CreateKelompokGuruWali.php`**:
```php
<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKelompokGuruWali extends CreateRecord
{
    protected static string $resource = KelompokGuruWaliResource::class;
}
```

**`Pages/EditKelompokGuruWali.php`**:
```php
<?php

namespace App\Filament\Akademik\Resources\GuruWali\Pages;

use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKelompokGuruWali extends EditRecord
{
    protected static string $resource = KelompokGuruWaliResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

### ✅ TES LANGKAH 5
```bash
php artisan route:list | grep guru-wali
```
**Ekspektasi**: Muncul route `/admin/guru-wali/kelompok`, `/admin/guru-wali/kelompok/create`, `/admin/guru-wali/kelompok/{record}/edit`.

**Tes Manual di Browser**:
1. Login ke `/admin`
2. Cek sidebar → muncul grup **"Guru Wali"** dengan menu **"Kelompok Dampingan"**
3. Klik menu → halaman daftar terbuka (tampil data seeder: Kelompok Dandelion, Kelompok Mawar)
4. Klik **"+ Tambah"** → form buka, isi semua field, Save → berhasil tersimpan
5. Klik **Edit** pada satu kelompok → ubah nama → Update berhasil

---

## LANGKAH 6 — Relation Manager: Kelola Siswa dalam Kelompok

Menambahkan tab **"Anggota Siswa"** pada halaman Edit Kelompok, sehingga Admin bisa tambah/hapus siswa langsung dari halaman kelompok.

**File yang dibuat**: `app/Filament/Akademik/Resources/GuruWali/RelationManagers/AnggotaSiswaRelationManager.php`

```php
<?php

namespace App\Filament\Akademik\Resources\GuruWali\RelationManagers;

use App\Models\Siswa;
use App\Models\KelompokGuruWaliSiswa;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class AnggotaSiswaRelationManager extends RelationManager
{
    protected static string $relationship = 'anggota';
    protected static ?string $title = 'Anggota Siswa';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('student_id')
                ->label('Pilih Siswa')
                ->required()
                ->searchable()
                ->options(function () {
                    // Tampilkan siswa aktif yang BELUM punya kelompok aktif lain
                    $sudahDikelompok = KelompokGuruWaliSiswa::where('status_aktif', true)
                        ->pluck('student_id');

                    return Siswa::whereNotIn('id', $sudahDikelompok)
                        ->where('status', 'aktif')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn ($s) => [$s->id => "{$s->name} ({$s->nisn})"])
                        ->toArray();
                })
                ->placeholder('Cari nama atau NISN siswa...'),

            Forms\Components\Toggle::make('status_aktif')
                ->label('Aktif di Kelompok')
                ->default(true),

            Forms\Components\TextInput::make('tahun_masuk')
                ->label('Tahun Masuk ke Kelompok')
                ->required()
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100)
                ->default(date('Y'))
                ->helperText('Tahun siswa ini bergabung ke kelompok dampingan ini'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('tahun_masuk')
                    ->label('Tahun Masuk')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('status_aktif')
                    ->label('Status')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('+ Tambah Siswa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // ⚠️ DESAIN: Bukan DeleteAction (hapus baris), melainkan custom action
                // yang men-set status_aktif = false (soft-archive).
                // Tujuan: riwayat siswa pernah didampingi Guru Wali tersebut tetap
                // tersimpan sebagai arsip dan dapat dilihat kembali oleh Admin.
                Tables\Actions\Action::make('keluarkan')
                    ->label('Keluarkan (Arsipkan)')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Keluarkan Siswa dari Kelompok')
                    ->modalDescription('Siswa akan diarsipkan (bukan dihapus). Riwayat pendampingan tetap tersimpan dan dapat dilihat Admin kapan saja.')
                    ->action(function ($record) {
                        $record->update(['status_aktif' => false]);

                        \Filament\Notifications\Notification::make()
                            ->title('Siswa Dikeluarkan dari Kelompok')
                            ->body('Data keanggotaan diarsipkan. Riwayat jurnal tetap tersimpan.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Belum ada siswa dalam kelompok ini')
            ->emptyStateDescription('Klik "+ Tambah Siswa" untuk menambahkan anggota.');
    }
}
```

**Update `KelompokGuruWaliResource.php`** — daftarkan relation manager:

```php
public static function getRelations(): array
{
    return [
        RelationManagers\AnggotaSiswaRelationManager::class,
    ];
}
```

### ✅ TES LANGKAH 6
**Tes Manual di Browser**:
1. Buka `/admin/guru-wali/kelompok`
2. Klik **Edit** pada salah satu kelompok
3. Gulir ke bawah → tampil tab/section **"Anggota Siswa"** dengan daftar siswa
4. Klik **"+ Tambah Siswa"** → dropdown siswa muncul (hanya siswa yang belum di kelompok lain)
5. Pilih siswa, Save → siswa masuk ke daftar anggota
6. Klik **"Keluarkan (Arsipkan)"** → konfirmasi muncul → setelah konfirmasi, `status_aktif` siswa berubah `false` (baris **tidak** dihapus dari DB)

---

## LANGKAH 7 — Validasi: 1 Siswa Hanya 1 Kelompok Aktif

Tambahkan validasi di form `AnggotaSiswaRelationManager` agar tidak bisa memasukkan siswa yang sudah aktif di kelompok lain.

**Update di `CreateAction` (langkah 6)** — tambahkan `before()` hook:

```php
Tables\Actions\CreateAction::make()
    ->label('+ Tambah Siswa')
    ->before(function (array $data, $action) {
        $sudahAktif = KelompokGuruWaliSiswa::where('student_id', $data['student_id'])
            ->where('status_aktif', true)
            ->exists();

        if ($sudahAktif) {
            $action->halt(); // Hentikan proses simpan

            \Filament\Notifications\Notification::make()
                ->title('Siswa Sudah dalam Kelompok Lain')
                ->body('Siswa ini sudah terdaftar sebagai anggota kelompok dampingan aktif. Satu siswa hanya boleh punya satu Guru Wali aktif.')
                ->danger()
                ->send();
        }
    }),
```

> **Catatan**: Dropdown siswa di Langkah 6 sudah memfilter siswa yang tidak tersedia,
> tapi validasi ini sebagai lapisan kedua (server-side) jika ada race condition.

### ✅ TES LANGKAH 7
**Skenario Tes**:
1. Masuk ke halaman Edit **Kelompok Dandelion**
2. Coba tambah siswa yang SUDAH ada di **Kelompok Mawar**
   - **Ekspektasi**: Siswa tersebut tidak muncul di dropdown (sudah difilter)
3. Simulasikan bypass via tinker: tambahkan data ganda langsung ke DB
4. Verifikasi validasi server-side menolak dan menampilkan notifikasi error

---

## LANGKAH 8 — Daftarkan ke AdminPanelProvider

Resource akan terdeteksi otomatis oleh Filament karena sudah menggunakan `discoverResources` untuk namespace `App\Filament\Akademik\Resources`.

**Verifikasi di `app/Providers/Filament/AdminPanelProvider.php`**:
```php
// Baris ini sudah ada — tidak perlu tambah apapun:
->discoverResources(in: app_path('Filament/Akademik/Resources'), for: 'App\Filament\Akademik\Resources')
```

Jika perlu tambah navigation item khusus, tambahkan di section `navigationItems()`.

### ✅ TES LANGKAH 8
```bash
php artisan route:clear && php artisan config:clear
php artisan route:list | grep guru-wali
```
**Ekspektasi**: Route muncul tanpa error.

```bash
php artisan filament:check-translations  # opsional
```

---

## LANGKAH 9 — Tes UI End-to-End

Skenario lengkap yang harus dijalankan satu per satu:

### Skenario A: Buat Kelompok Baru
- [ ] Login ke `/admin`
- [ ] Navigasi ke **Guru Wali → Kelompok Dampingan**
- [ ] Klik **"+ Tambah Kelompok"**
- [ ] Isi: Nama = "Kelompok Anggrek", Guru Wali = [pilih guru], Tahun Masuk = 2024, Status = Aktif
- [ ] Klik **Save** → berhasil, redirect ke halaman daftar
- [ ] Kelompok baru muncul di tabel

### Skenario B: Edit Kelompok & Tambah Siswa
- [ ] Klik **Edit** pada "Kelompok Anggrek"
- [ ] Ubah Guru Wali ke guru lain → Update berhasil
- [ ] Di section **"Anggota Siswa"** → klik **"+ Tambah Siswa"**
- [ ] Pilih 3 siswa satu per satu → semua berhasil masuk
- [ ] Jumlah siswa di kolom tabel berubah menjadi 3

### Skenario C: Validasi Duplikasi
- [ ] Coba tambah siswa yang sudah ada di kelompok lain
- [ ] Sistem TIDAK menampilkan siswa tersebut di dropdown
- [ ] Jika ada bypass → notifikasi error muncul

### Skenario D: Nonaktifkan Kelompok
- [ ] Klik **Edit** pada sebuah kelompok
- [ ] Toggle **Status Aktif** → OFF
- [ ] Save → kelompok tersebut masih ada tapi berstatus nonaktif
- [ ] Filter di tabel tabel berjalan benar (filter Aktif/Nonaktif)

---

## CATATAN TEKNIS

### Naming Convention
- Model: `KelompokGuruWali` (bukan `GuruWaliKelompok`)
- Tabel: `kelompok_guru_wali`, `kelompok_guru_wali_siswa`
- FK ke guru: `teacher_id` (konsisten dengan `teachers.id`)
- FK ke siswa: `student_id` (konsisten dengan `students.id`)

### SoftDeletes & Soft-Archive
- `kelompok_guru_wali` → pakai SoftDeletes
- `kelompok_guru_wali_siswa` → **TIDAK** pakai SoftDeletes. Pengeluaran siswa dilakukan dengan men-set `status_aktif = false` (soft-archive), bukan menghapus baris. Ini menjaga riwayat siswa pernah didampingi Guru Wali tersebut tetap tersimpan di database untuk keperluan arsip dan audit.

### Auto-Arsip via Listener (Event-Driven)
- Saat `students.status` berubah ke `'lulus'` atau `'mutasi'` (via `GraduateStudentAction` atau `MutateStudentAction`), event yang ter-dispatch akan ditangkap oleh Listener (`HandleGuruWaliStudentGraduated` / `HandleGuruWaliStudentMutated`) yang secara otomatis men-set `status_aktif = false` pada baris aktif `kelompok_guru_wali_siswa` milik siswa tersebut.
- Jurnal di `jurnal_guru_wali` **tidak disentuh** — tetap sebagai arsip riwayat pendampingan.

### Filament Namespace
Resource ini masuk ke `App\Filament\Akademik\Resources` → terdeteksi otomatis oleh `discoverResources`.

---

*Setelah semua langkah di dokumen ini selesai dan lulus tes, lanjut ke:*
*→ `langkah-implementasi-portal-guru.md` (Input Jurnal & Pemantauan)*
ENDOFFILE
