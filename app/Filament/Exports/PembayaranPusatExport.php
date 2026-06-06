<?php

namespace App\Filament\Exports;

use App\Models\PembayaranPusat;
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

class PembayaranPusatExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Builder $query) {}

    public function query(): Builder
    {
        return $this->query->with(['ctk:id,nama_lengkap,nik', 'creator:id,name']);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Entity',
            'Nama CTK',
            'NIK CTK',
            'Tanggal Pembayaran',
            'Nominal',
            'Keterangan',
            'Dibuat Oleh',
            'Dibuat Pada',
            'Diperbarui Pada',
        ];
    }

    /**
     * @param  PembayaranPusat  $pembayaran
     */
    public function map($pembayaran): array
    {
        return [
            $pembayaran->id,
            $pembayaran->entity?->value ?? '',
            $pembayaran->ctk?->nama_lengkap ?? '',
            $pembayaran->ctk?->nik ?? '',
            $pembayaran->tanggal_pembayaran?->format('Y-m-d') ?? '',
            $pembayaran->nominal !== null ? 'Rp '.number_format((float) $pembayaran->nominal, 0, ',', '.') : '',
            $pembayaran->keterangan ?? '',
            $pembayaran->creator?->name ?? '',
            $pembayaran->created_at?->format('Y-m-d H:i:s') ?? '',
            $pembayaran->updated_at?->format('Y-m-d H:i:s') ?? '',
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
            'B' => 12,
            'C' => 28,
            'D' => 20,
            'E' => 16,
            'F' => 18,
            'G' => 42,
            'H' => 22,
            'I' => 20,
            'J' => 20,
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
                    $worksheet->getStyle("D2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("H2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("F2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $worksheet->getStyle("C2:C{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("G2:G{$highestRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
