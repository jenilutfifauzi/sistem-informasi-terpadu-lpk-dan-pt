<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class ScreeningSection
{
    public static function make(): Section
    {
        return Section::make('6-7. Screening Interview')
            ->description(fn ($record) => self::getStatusBadge($record, 6, 7) ?? 'Pencatatan screening interview di PT')
            ->schema([
                Placeholder::make('screening_summary')
                    ->label('Ringkasan Screening')
                    ->content(function ($record) {
                        if (! $record) {
                            return 'Belum ada data screening';
                        }

                        $screenings = $record->screenings;
                        $totalScreenings = $screenings->count();
                        $lolosCount = $screenings->where('screening_result', 'Lolos')->count();

                        $status = $lolosCount > 0 ? 'Lolos' : 'Belum Lolos';

                        return new HtmlString("
                            <div class='text-sm space-y-1'>
                                <div><span class='font-semibold'>Total Screening:</span> {$totalScreenings}</div>
                                <div><span class='font-semibold'>Lolos:</span> {$lolosCount}/{$totalScreenings}</div>
                                <div><span class='font-semibold'>Status:</span> <span class='font-bold'>{$status}</span></div>
                            </div>
                        ");
                    }),

                Repeater::make('screenings')
                    ->relationship('screenings')
                    ->label('Daftar Screening')
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
                    ->itemLabel(function (array $state): ?string {
                        $interviewer = isset($state['interviewer_id']) ? User::find($state['interviewer_id'])?->name : null;
                        $result = $state['screening_result'] ?? 'Pending';

                        return $interviewer ? "{$interviewer} - {$result}" : 'Screening baru';
                    })
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Screening')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed(false);
    }

    protected static function getStatusBadge($record, int ...$stages): ?string
    {
        if (! $record) {
            return null;
        }

        $statuses = [];
        foreach ($stages as $stage) {
            $stageAttribute = "stage{$stage}_complete";
            $isComplete = $record->$stageAttribute ?? false;
            $icon = $isComplete ? '✅' : '⬜';
            $statuses[] = "{$icon} Stage {$stage}";
        }

        return implode(' | ', $statuses);
    }
}
