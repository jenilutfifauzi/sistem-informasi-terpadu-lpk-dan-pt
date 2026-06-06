<?php

namespace App\Filament\Exports;

use App\Models\EmployeeLPK;
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

class EmployeeLPKExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
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
    }

    public function map($employee): array
    {
        /** @var EmployeeLPK $employee */
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
            $employee->honor_pokok !== null ? 'Rp '.number_format((float) $employee->honor_pokok, 0, ',', '.') : '',
            $employee->honor_per_jam !== null ? 'Rp '.number_format((float) $employee->honor_per_jam, 0, ',', '.') : '',
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
            'B' => 28,
            'C' => 28,
            'D' => 18,
            'E' => 40,
            'F' => 16,
            'G' => 18,
            'H' => 18,
            'I' => 16,
            'J' => 18,
            'K' => 18,
            'L' => 18,
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

                    $worksheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("F2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("K2:L{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $worksheet->getStyle("B2:E{$highestRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
