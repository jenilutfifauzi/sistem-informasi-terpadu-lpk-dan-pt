<?php

namespace App\Filament\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeLPKExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Email',
            'Telepon',
            'Alamat',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Jabatan',
            'Status',
            'Tanggal Bergabung',
            'Honor Pokok',
            'Honor Per Jam',
        ];
        // Note: NIK excluded per security requirements (FR-009)
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->nama_lengkap,
            $employee->email,
            $employee->telepon,
            $employee->alamat,
            $employee->tanggal_lahir?->format('Y-m-d'),
            $employee->jenis_kelamin,
            $employee->jabatan?->getLabel() ?? $employee->jabatan,
            $employee->status?->getLabel() ?? $employee->status,
            $employee->tanggal_bergabung?->format('Y-m-d'),
            $employee->honor_pokok,
            $employee->honor_per_jam,
        ];
    }
}
