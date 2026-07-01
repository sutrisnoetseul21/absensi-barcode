# 🗂️ Integrasi Filament v4 + Livewire v3

> [!IMPORTANT]
> **Pesan untuk AI Coding Agent:**
> Panduan ini khusus mengatur integrasi antara **Livewire v3** dan **Filament v4**. Baca dokumen ini sebelum Anda membuat Custom Pages, Widgets, atau meng-embed komponen Livewire ke dalam halaman panel Filament.

Filament v4 berjalan di atas Livewire v3 dan menyediakan panel admin yang kaya fitur secara out of the box. Panduan ini memastikan AI agent menggunakan pola yang benar untuk Filament v4, bukan v3 atau v2.

---

## 1. Syarat Kompatibilitas

> [!CAUTION]
> **Hanya Filament v4+ yang kompatibel dengan Livewire v3.**
> Filament v2.x atau v3.x yang terikat pada Livewire v2 **TIDAK BISA** dan **TIDAK BOLEH** dipadukan dengan Livewire v3. Pastikan proyek sudah menggunakan Filament v4 sebelum mencoba integrasi.

```bash
# Cek versi Filament:
cat composer.json | grep filament
# Harus: "filament/filament": "^4.0"
```

---

## 2. Instalasi Filament v4

```bash
composer require filament/filament:"^4.0"
php artisan filament:install --panels
```

Perintah ini akan:
- Membuat `app/Providers/Filament/AdminPanelProvider.php`
- Membuat model & migration untuk `User` jika belum ada
- Menambahkan trait `HasFilamentPanels` ke model User (jika dipilih)

### Konfigurasi Panel (`AdminPanelProvider.php`):

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                // ...
            ]);
    }
}
```

---

## 3. Membuat Resource

```bash
# Resource dengan CRUD lengkap (generate otomatis dari model):
php artisan make:filament-resource Post --generate

# Resource sederhana (tanpa auto-generate):
php artisan make:filament-resource Post

# Resource dengan soft delete:
php artisan make:filament-resource Post --generate --soft-deletes
```

### Contoh Resource Lengkap:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Post')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft'     => 'Draft',
                            'published' => 'Published',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\RichEditor::make('body')
                        ->label('Konten')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
```

---

## 4. Membuat Custom Page & Widget

```bash
# Buat custom page:
php artisan make:filament-page Dashboard

# Buat widget:
php artisan make:filament-widget StatsOverview --stats-overview
php artisan make:filament-widget LatestPosts --table
php artisan make:filament-widget RevenueChart --chart
```

### Custom Page Filament (Pola yang Benar):

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $navigationLabel = 'Dashboard';

    public function getTitle(): string
    {
        return 'Dashboard Utama';
    }
}
```

```html
{{-- resources/views/filament/pages/dashboard.blade.php --}}
<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900">Total Users</h3>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ \App\Models\User::count() }}</p>
        </div>
    </div>
</x-filament-panels::page>
```

### Stats Overview Widget:

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', \App\Models\User::count())
                ->description('Semua user terdaftar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Post Published', \App\Models\Post::where('status', 'published')->count())
                ->description('Post aktif')
                ->color('primary'),
            Stat::make('Draft', \App\Models\Post::where('status', 'draft')->count())
                ->color('warning'),
        ];
    }
}
```

---

## 5. Menggabungkan Livewire v3 ke dalam Halaman Filament

Karena Filament membutuhkan class PHP tradisional untuk Pages dan Widgets, gunakan pola berikut untuk memanfaatkan Livewire di dalam Filament:

### ✅ BENAR: Embed Komponen Livewire ke dalam View Filament

**Langkah 1:** Buat komponen Livewire biasa:
```bash
php artisan make:livewire StatsCalculator
```

**Langkah 2:** Embed ke dalam view Filament:
```html
{{-- resources/views/filament/pages/my-custom-page.blade.php --}}
<x-filament-panels::page>
    {{-- Embed komponen Livewire biasa --}}
    <livewire:stats-calculator />
</x-filament-panels::page>
```

**Mengapa pola ini penting?**
Filament mengelola lifecycle halaman melalui class tradisional PHP, sementara komponen Livewire tetap bisa digunakan untuk widget interaktif di dalamnya.

---

## 6. Relation Manager

```bash
php artisan make:filament-relation-manager PostResource comments body
```

```php
<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('body')
                ->label('Komentar')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('body')->label('Komentar'),
                Tables\Columns\TextColumn::make('user.name')->label('Pengguna'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

---

## 7. Form Components yang Sering Dipakai

```php
// Text input
Forms\Components\TextInput::make('name')
    ->label('Nama')
    ->required()
    ->minLength(3)
    ->maxLength(255),

// Textarea
Forms\Components\Textarea::make('description')
    ->rows(3),

// Rich editor
Forms\Components\RichEditor::make('content'),

// Select (dropdown)
Forms\Components\Select::make('status')
    ->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])
    ->default('active'),

// Select dengan relasi
Forms\Components\Select::make('category_id')
    ->relationship('category', 'name')
    ->searchable()
    ->preload(),

// Date picker
Forms\Components\DatePicker::make('published_at')
    ->label('Tanggal Publish'),

// Toggle
Forms\Components\Toggle::make('is_featured')
    ->label('Featured?'),

// File upload
Forms\Components\FileUpload::make('thumbnail')
    ->image()
    ->directory('thumbnails'),

// Repeater
Forms\Components\Repeater::make('items')
    ->schema([
        Forms\Components\TextInput::make('name'),
        Forms\Components\TextInput::make('price')->numeric(),
    ]),
```

---

## 8. Table Columns yang Sering Dipakai

```php
// Text column
Tables\Columns\TextColumn::make('name')
    ->searchable()
    ->sortable(),

// Badge column (status)
Tables\Columns\BadgeColumn::make('status')
    ->colors([
        'success' => 'active',
        'warning' => 'pending',
        'danger'  => 'inactive',
    ]),

// Boolean column
Tables\Columns\IconColumn::make('is_featured')
    ->boolean(),

// Image column
Tables\Columns\ImageColumn::make('thumbnail')
    ->circular(),

// Date column
Tables\Columns\TextColumn::make('created_at')
    ->dateTime('d M Y, H:i')
    ->sortable(),

// Relasi column
Tables\Columns\TextColumn::make('user.name')
    ->label('User')
    ->searchable(),
```

---

## 🛑 Ringkasan Aturan Integrasi AI

| Kategori | ❌ JANGAN | ✅ LAKUKAN |
|----------|-----------|-----------|
| **Versi** | Campur Filament v2/v3 dengan Livewire v3 | Pastikan Filament v4+ |
| **Page/Widget** | Bikin Page/Widget tanpa class PHP | Gunakan class tradisional (`class X extends Page`) |
| **Livewire di Filament** | Hindari komponen Livewire di admin panel | Buat Livewire component biasa, embed via `<livewire:komponen />` |
| **Style** | Tailwind v4 class di template Filament | Tailwind v3 class (sesuai stack) |
| **Instalasi** | Install Filament tanpa `--panels` flag | `php artisan filament:install --panels` |

---

*Dokumentasi ini untuk stack Laravel 12 + Livewire v3 + Tailwind v3 + Alpine v3 + Filament v4.*
