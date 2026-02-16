<?php

namespace App\Filament\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CTKExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        // Eager load relationships for export
        return $this->query->with(['screenings', 'mcuRecords']);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Email',
            'No. Telepon',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'Status Saat Ini',
            'Stage Saat Ini',
            'Entitas Saat Ini',
            'Status Screening',
            'Status MCU',
            'Tanggal Dibuat',
        ];
        // Note: NIK, paspor_number, visa_number excluded per security requirements (FR-009)
    }

    public function map($ctk): array
    {
        // Get latest screening status
        $latestScreening = $ctk->screenings->sortByDesc('created_at')->first();
        $screeningStatus = $latestScreening ? $latestScreening->screening_result : 'Belum Ada';

        // Get latest MCU status
        $latestMCU = $ctk->mcuRecords->sortByDesc('created_at')->first();
        $mcuStatus = $latestMCU ? $latestMCU->status?->getLabel() ?? $latestMCU->status : 'Belum Ada';

        return [
            $ctk->id,
            $ctk->nama_lengkap,
            $ctk->email,
            $ctk->no_telepon,
            $ctk->tanggal_lahir?->format('Y-m-d'),
            $ctk->jenis_kelamin,
            $ctk->alamat,
            $ctk->current_status?->getLabel() ?? $ctk->current_status,
            $ctk->current_stage,
            $ctk->current_entity?->value ?? $ctk->current_entity,
            $screeningStatus,
            $mcuStatus,
            $ctk->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
