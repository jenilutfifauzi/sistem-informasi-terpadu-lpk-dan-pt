<?php

namespace App\Filament\Exports;

use App\Models\PembayaranPusat;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PembayaranPusatExport implements FromQuery, WithHeadings, WithMapping
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
            'Rp '.number_format($pembayaran->nominal, 0, ',', '.'),
            $pembayaran->keterangan ?? '',
            $pembayaran->creator?->name ?? '',
            $pembayaran->created_at?->format('Y-m-d H:i:s') ?? '',
            $pembayaran->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
