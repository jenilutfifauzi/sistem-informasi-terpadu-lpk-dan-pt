<?php

namespace App\Helpers;

use App\Enums\AssetCategory;
use App\Enums\EntityType;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class AssetNumberGenerator
{
    /**
     * Generate unique asset nomor_inventaris with format: [PT/LPK]-[KATEGORI_ABBR]-[TAHUN]-[SEQUENCE]
     *
     * Example: LPK-ELK-2024-001, PT-FRN-2025-015
     *
     * @param  EntityType  $entity  The entity (PT or LPK)
     * @param  AssetCategory  $kategori  The asset category
     * @param  int  $tahun  The purchase year
     * @return string The generated nomor_inventaris
     */
    public static function generate(EntityType $entity, AssetCategory $kategori, int $tahun): string
    {
        return DB::transaction(function () use ($entity, $kategori, $tahun) {
            $prefix = "{$entity->value}-{$kategori->abbreviation()}-{$tahun}";

            // Get last sequence for this prefix with row locking to prevent race conditions
            $lastAsset = Asset::where('nomor_inventaris', 'LIKE', "{$prefix}-%")
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_inventaris, "-", -1) AS UNSIGNED) DESC')
                ->first();

            $sequence = $lastAsset
                ? ((int) explode('-', $lastAsset->nomor_inventaris)[3]) + 1
                : 1;

            // Format: PT-ELK-2024-001 (3-digit zero-padded sequence)
            return sprintf('%s-%03d', $prefix, $sequence);
        });
    }
}
