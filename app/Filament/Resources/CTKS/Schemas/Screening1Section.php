<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Enums\ScreeningStage;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class Screening1Section
{
    public static function make(): Section
    {
        return Section::make('6. Screening 1')
            ->description(fn ($record) => self::getStatusBadge($record, 6) ?? 'Pencatatan screening tahap 1 di PT')
            ->schema([
                Repeater::make('screening1Records')
                    ->relationship('screenings', modifyQueryUsing: fn ($query) => $query->where('screening_stage', 'Screening 1'))
                    ->label('Daftar Screening 1')
                    ->schema([
                        Select::make('interviewer_id')
                            ->label('Pewawancara')
                            ->options(function () {
                                return User::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Pilih pewawancara dari daftar user'),

                        DatePicker::make('interview_date')
                            ->label('Tanggal Interview')
                            ->required()
                            ->maxDate(now())
                            ->helperText('Tanggal pelaksanaan interview'),

                        TextInput::make('interview_location')
                            ->label('Lokasi Interview')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kantor PT, Ruang Meeting'),

                        Select::make('screening_result')
                            ->label('Hasil Screening')
                            ->options([
                                'Lolos' => 'Lolos',
                                'Tidak Lolos' => 'Tidak Lolos',
                            ])
                            ->default('Lolos')
                            ->required(),

                        Textarea::make('interview_notes')
                            ->label('Catatan Interview')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Catatan terkait hasil interview'),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['screening_stage'] = ScreeningStage::Screening1->value;

                        return $data;
                    })
                    ->itemLabel(function (array $state): ?string {
                        $interviewer = isset($state['interviewer_id']) ? User::find($state['interviewer_id'])?->name : null;
                        $result = $state['screening_result'] ?? 'Pending';

                        return $interviewer ? "{$interviewer} - {$result}" : 'Screening 1 baru';
                    })
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Screening 1')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->columns(1);
    }

    protected static function getStatusBadge($record, int $stage): ?string
    {
        if (! $record) {
            return null;
        }

        $stageAttribute = "stage{$stage}_complete";
        $isComplete = $record->$stageAttribute ?? false;
        $icon = $isComplete ? '✅' : '⬜';
        $status = $isComplete ? 'Selesai' : 'Belum Selesai';

        return "{$icon} Stage {$stage}: {$status}";
    }
}
