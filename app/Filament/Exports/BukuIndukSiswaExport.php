<?php

namespace App\Filament\Exports;

use App\Models\BukuIndukSiswa;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BukuIndukSiswaExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
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
        /** @var BukuIndukSiswa $record */
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

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 18,
            'C' => 24,
            'D' => 20,
            'E' => 18,
            'F' => 16,
            'G' => 20,
            'H' => 16,
            'I' => 18,
            'J' => 18,
            'K' => 20,
            'L' => 22,
            'M' => 20,
            'N' => 40,
            'O' => 18,
            'P' => 28,
            'Q' => 40,
            'R' => 18,
            'S' => 18,
            'T' => 28,
            'U' => 24,
            'V' => 16,
            'W' => 16,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $worksheet = $event->sheet->getDelegate();
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $dataRange = "A1:{$highestColumn}{$highestRow}";

                $worksheet->freezePane('A2');
                $worksheet->setAutoFilter($dataRange);
                $worksheet->getRowDimension(1)->setRowHeight(28);

                $worksheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                    ],
                ]);

                if ($highestRow >= 2) {
                    for ($row = 2; $row <= $highestRow; $row++) {
                        if ($row % 2 === 0) {
                            $worksheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8FAFC'],
                                ],
                            ]);
                        }
                    }

                    $worksheet->getStyle("A2:U{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("B2:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("V2:W{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
