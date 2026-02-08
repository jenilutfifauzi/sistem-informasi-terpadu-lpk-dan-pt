<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Enums\JabatanLPK;
use App\Models\EmployeeLPK;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class TrainingSection
{
    public static function make(): Section
    {
        return Section::make('Pelatihan di LPK')
            ->description('Pencatatan pelatihan CTK di LPK dengan instruktur')
            ->schema([
                Placeholder::make('training_summary')
                    ->label('Ringkasan Pelatihan')
                    ->content(function ($record) {
                        if (! $record) {
                            return 'Belum ada data pelatihan';
                        }

                        $trainings = $record->trainings;
                        $totalTrainings = $trainings->count();
                        $completedTrainings = $trainings->where('completion_status', 'Selesai')->count();
                        $totalHours = $trainings->sum('training_hours');

                        return new HtmlString("
                            <div class='text-sm space-y-1'>
                                <div><span class='font-semibold'>Total Pelatihan:</span> {$totalTrainings}</div>
                                <div><span class='font-semibold'>Selesai:</span> {$completedTrainings}/{$totalTrainings}</div>
                                <div><span class='font-semibold'>Total Jam Pelatihan:</span> {$totalHours} jam</div>
                            </div>
                        ");
                    }),

                Repeater::make('trainings')
                    ->relationship('trainings')
                    ->label('Daftar Pelatihan')
                    ->schema([
                        Select::make('instructor_id')
                            ->label('Instruktur')
                            ->options(function () {
                                return EmployeeLPK::where('jabatan', JabatanLPK::Instruktur)
                                    ->get()
                                    ->pluck('nama_lengkap', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Pilih instruktur dari karyawan LPK'),

                        DatePicker::make('training_start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->maxDate(now())
                            ->helperText('Tanggal mulai pelatihan'),

                        DatePicker::make('training_end_date')
                            ->label('Tanggal Selesai')
                            ->maxDate(now())
                            ->afterOrEqual('training_start_date')
                            ->helperText('Tanggal selesai pelatihan (opsional)'),

                        TextInput::make('training_location')
                            ->label('Lokasi Pelatihan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Ruang Pelatihan A, Gedung LPK'),

                        TextInput::make('training_hours')
                            ->label('Jumlah Jam')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('jam')
                            ->helperText('Total jam pelatihan'),

                        Select::make('completion_status')
                            ->label('Status Penyelesaian')
                            ->options([
                                'Belum Selesai' => 'Belum Selesai',
                                'Selesai' => 'Selesai',
                            ])
                            ->default('Belum Selesai')
                            ->required(),

                        Textarea::make('completion_notes')
                            ->label('Catatan Penyelesaian')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Catatan terkait penyelesaian pelatihan'),
                    ])
                    ->itemLabel(function (array $state): ?string {
                        $instructor = isset($state['instructor_id']) ? EmployeeLPK::find($state['instructor_id'])?->nama_lengkap : null;
                        $status = $state['completion_status'] ?? 'Belum Selesai';

                        return $instructor ? "{$instructor} - {$status}" : 'Pelatihan baru';
                    })
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Pelatihan')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed(false);
    }
}
