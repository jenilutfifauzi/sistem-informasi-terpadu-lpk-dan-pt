<?php

namespace App\Filament\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeePTExport implements FromQuery, WithHeadings, WithMapping
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
            'NIK',
            'Nama Lengkap',
            'Email',
            'Jabatan',
            'Divisi',
            'Status',
            'Jenis Kontrak',
            'Tanggal Bergabung',
            'Gaji Pokok',
            'Tunjangan',
            'Tanggal Dibuat',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->nik,
            $employee->nama_lengkap,
            $employee->email,
            $employee->jabatan?->getLabel() ?? $employee->jabatan,
            $employee->divisi?->getLabel() ?? $employee->divisi,
            $employee->status?->getLabel() ?? $employee->status,
            $employee->jenis_kontrak?->getLabel() ?? $employee->jenis_kontrak,
            $employee->tanggal_bergabung?->format('Y-m-d'),
            $employee->gaji_pokok,
            $employee->tunjangan,
            $employee->created_at?->format('Y-m-d'),
        ];
    }
}
