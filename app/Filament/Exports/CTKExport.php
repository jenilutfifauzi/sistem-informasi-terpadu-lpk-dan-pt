<?php

namespace App\Filament\Exports;

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

class CTKExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
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
    }

    public function map($ctk): array
    {
        $latestScreening = $ctk->screenings->sortByDesc('created_at')->first();
        $screeningStatus = $latestScreening ? $latestScreening->screening_result : 'Belum Ada';

        $latestMCU = $ctk->mcuRecords->sortByDesc('created_at')->first();
        $mcuStatus = $latestMCU ? ($latestMCU->status?->getLabel() ?? $latestMCU->status) : 'Belum Ada';

        return [
            $ctk->id,
            $ctk->nama_lengkap,
            $ctk->email,
            $ctk->no_telepon,
            $ctk->tanggal_lahir?->format('Y-m-d') ?? '',
            $ctk->jenis_kelamin,
            $ctk->alamat,
            $ctk->current_status?->getLabel() ?? $ctk->current_status,
            $ctk->current_stage,
            $ctk->current_entity?->value ?? $ctk->current_entity,
            $screeningStatus,
            $mcuStatus,
            $ctk->created_at?->format('Y-m-d H:i:s') ?? '',
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
            'C' => 32,
            'D' => 20,
            'E' => 14,
            'F' => 16,
            'G' => 40,
            'H' => 22,
            'I' => 14,
            'J' => 16,
            'K' => 18,
            'L' => 16,
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

                    $worksheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("D2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("I2:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("B2:C{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("G2:G{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("H2:H{$highestRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
