<?php

namespace App\Filament\Akademik\Resources\Siswa\RelationManagers;

use App\Models\StudentGuardian;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentGuardianRelationManager extends RelationManager
{
    protected static string $relationship = 'guardians';

    protected static ?string $title = 'Orang Tua / Wali';

    protected static ?string $modelLabel = 'Data Wali';

    protected static ?string $pluralModelLabel = 'Data Orang Tua / Wali';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'ayah' => 'Ayah Kandung',
                        'ibu'  => 'Ibu Kandung',
                        'wali' => 'Wali',
                    ])
                    ->required()
                    ->live()
                    ->rule(function ($get, $record) {
                        // Validasi: type 'ayah' dan 'ibu' hanya boleh ada satu per siswa
                        return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if (!in_array($value, ['ayah', 'ibu'])) {
                                return; // 'wali' boleh lebih dari satu
                            }

                            $studentId = $this->ownerRecord->id;

                            $exists = \App\Models\StudentGuardian::where('student_id', $studentId)
                                ->where('type', $value)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->exists();

                            if ($exists) {
                                $label = $value === 'ayah' ? 'Ayah Kandung' : 'Ibu Kandung';
                                $fail("Siswa ini sudah memiliki data {$label}. Hapus data lama terlebih dahulu sebelum menambah yang baru.");
                            }
                        };
                    }),

                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('occupation')
                    ->label('Pekerjaan')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('phone')
                    ->label('Nomor HP / WA')
                    ->tel()
                    ->maxLength(50)
                    ->nullable()
                    ->helperText('Contoh: 081234567890'),

                Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(1000)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state) => StudentGuardian::typeLabels()[$state] ?? ucfirst($state))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'ayah' => 'info',
                        'ibu'  => 'success',
                        'wali' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('occupation')
                    ->label('Pekerjaan')
                    ->placeholder('-'),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->placeholder('-'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Wali/Orang Tua'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data orang tua / wali')
            ->emptyStateDescription('Klik tombol "Tambah Wali/Orang Tua" untuk menambahkan data.');
    }
}
