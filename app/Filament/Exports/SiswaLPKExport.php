<?php

namespace App\Filament\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaLPKExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No. Urut',
            'Nomor Induk',
            'Nama Siswa',
            'Jenis Kelamin',
            'No. HP',
            'Email',
            'Program Pendidikan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Tanggal Masuk',
            'Alamat',
            'Agama',
            'Pendidikan Terakhir',
        ];
    }

    public function map($record): array
    {
        return [
            $record->nomor_urut,
            $record->nomor_induk,
            $record->nama_siswa,
            $record->jenis_kelamin,
            $record->no_hp,
            $record->email,
            $record->program_pendidikan,
            $record->tempat_lahir,
            $record->tanggal_lahir?->format('Y-m-d'),
            $record->tanggal_masuk?->format('Y-m-d'),
            $record->alamat,
            $record->agama,
            $record->pendidikan_terakhir,
        ];
    }
}
