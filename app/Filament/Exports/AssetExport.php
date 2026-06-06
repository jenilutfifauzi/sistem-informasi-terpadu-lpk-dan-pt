<?php

namespace App\Filament\Exports;

use App\Models\Asset;
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

class AssetExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Builder $query) {}

    public function query(): Builder
    {
        // Eager load relationships to avoid N+1 queries
        return $this->query->with(['currentAssignment.assignable']);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nomor Inventaris',
            'Nama Barang',
            'Kategori',
            'Jumlah',
            'Satuan',
            'Kondisi',
            'Status Assignment',
            'Assigned To',
            'Assigned Date',
            'Tahun Pembelian',
            'Nilai Pembelian',
            'Lokasi',
            'Deskripsi',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param  Asset  $asset
     */
    public function map($asset): array
    {
        return [
            $asset->id,
            $asset->nomor_inventaris,
            $asset->nama_barang,
            $asset->kategori?->getLabel() ?? $asset->kategori?->value ?? '',
            $asset->jumlah,
            $asset->satuan,
            $asset->kondisi?->getLabel() ?? $asset->kondisi?->value ?? '',
            $asset->status_assignment?->getLabel() ?? $asset->status_assignment?->value ?? '',
            $this->getAssignedToName($asset),
            $asset->currentAssignment?->assigned_date?->format('Y-m-d') ?? '',
            $asset->tahun_pembelian,
            $asset->nilai_pembelian !== null ? 'Rp '.number_format((float) $asset->nilai_pembelian, 0, ',', '.') : '',
            $asset->lokasi,
            $asset->deskripsi,
            $asset->created_at?->format('Y-m-d H:i:s') ?? '',
            $asset->updated_at?->format('Y-m-d H:i:s') ?? '',
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
            'D' => 18,
            'E' => 10,
            'F' => 12,
            'G' => 16,
            'H' => 18,
            'I' => 24,
            'J' => 16,
            'K' => 14,
            'L' => 18,
            'M' => 20,
            'N' => 40,
            'O' => 20,
            'P' => 20,
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
                    $worksheet->getStyle("E2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("J2:K{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle("L2:L{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $worksheet->getStyle("C2:C{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("I2:I{$highestRow}")->getAlignment()->setWrapText(true);
                    $worksheet->getStyle("M2:N{$highestRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }

    protected function getAssignedToName(Asset $asset): string
    {
        if (! $asset->currentAssignment) {
            return '';
        }

        $assignable = $asset->currentAssignment->assignable;

        if (! $assignable) {
            return '';
        }

        // Handle different assignable types
        if (method_exists($assignable, 'nama_lengkap') || isset($assignable->nama_lengkap)) {
            return $assignable->nama_lengkap ?? '';
        }

        if (method_exists($assignable, 'name') || isset($assignable->name)) {
            return $assignable->name ?? '';
        }

        return '';
    }
}
