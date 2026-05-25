<?php

namespace App\Filament\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BukuIndukSiswaExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Nomor Induk',
            'Program Pendidikan',
            'Program Bahasa',
            'Nama Panggilan',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Kewarganegaraan',
            'Status Perkawinan',
            'Nama Suami / Istri',
            'No. HP Suami / Istri',
            'Alamat Siswa',
            'No. HP Siswa',
            'Email',
            'Alamat Orang Tua',
            'No. HP Orang Tua',
            'Golongan Darah',
            'Penyakit Pernah Diderita',
            'Kelainan Jasmani',
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($record): array
    {
        return [
            $record->nama_lengkap,
            $record->nomor_induk,
            $record->program_pendidikan,
            $record->program_bahasa,
            $record->nama_panggilan,
            $record->jenis_kelamin,
            $record->tempat_lahir,
            $record->tanggal_lahir?->format('Y-m-d'),
            $record->agama,
            $record->kewarganegaraan,
            $record->status_perkawinan,
            $record->nama_suami_istri,
            $record->no_hp_suami_istri,
            $record->alamat_siswa,
            $record->no_hp_siswa,
            $record->email,
            $record->alamat_orang_tua,
            $record->no_hp_orang_tua,
            $record->golongan_darah,
            $record->penyakit_pernah_diderita,
            $record->kelainan_jasmani,
            $record->tinggi_badan_cm,
            $record->berat_badan_kg,
        ];
    }
}
