<?php

namespace App\Filament\Exports;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetExport implements FromQuery, WithHeadings, WithMapping
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
            $asset->nilai_pembelian ? 'Rp '.number_format($asset->nilai_pembelian, 0, ',', '.') : '',
            $asset->lokasi,
            $asset->deskripsi,
            $asset->created_at?->format('Y-m-d H:i:s') ?? '',
            $asset->updated_at?->format('Y-m-d H:i:s') ?? '',
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
