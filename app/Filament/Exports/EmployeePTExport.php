<?php

namespace App\Filament\Exports;

use App\Models\EmployeePT;
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

class EmployeePTExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
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
        /** @var EmployeePT $employee */
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
            $employee->gaji_pokok !== null ? 'Rp '.number_format((float) $employee->gaji_pokok, 0, ',', '.') : '',
            $employee->tunjangan !== null ? 'Rp '.number_format((float) $employee->tunjangan, 0, ',', '.') : '',
            $employee->created_at?->format('Y-m-d'),
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
            'B' => 20,
            'C' => 28,
            'D' => 28,
            'E' => 18,
            'F' => 18,
            'G' => 16,
            'H' => 18,
            'I' => 18,
            'J' => 18,
            'K' => 18,
            'L' => 16,
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
                    $worksheet->getStyle("E2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("J2:K{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $worksheet->getStyle("C2:D{$highestRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
