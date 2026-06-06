<?php

namespace App\Filament\Exports;

use App\Models\SiswaLPK;
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

class SiswaLPKExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
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
        /** @var SiswaLPK $record */
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
            'A' => 10,
            'B' => 18,
            'C' => 28,
            'D' => 16,
            'E' => 18,
            'F' => 28,
            'G' => 24,
            'H' => 20,
            'I' => 16,
            'J' => 16,
            'K' => 40,
            'L' => 18,
            'M' => 20,
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
                $worksheet->getRowDimension(1)->setRowHeight(24);

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

                    $worksheet->getStyle("A2:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("D2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("C2:K{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("L2:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
