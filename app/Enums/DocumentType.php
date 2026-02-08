<?php

namespace App\Enums;

enum DocumentType: string
{
    case SoalBerkas = 'Soal/Berkas';
    case Paspor = 'Paspor';
    case IjinDesa = 'Ijin Desa';
    case Rekomendasi = 'Rekomendasi';
    case WorkingPermit = 'Working Permit';
    case VisaDocument = 'Visa Document';
    case MedicalFullReport = 'Medical Full Report';
    case OPPDocument = 'OPP Document';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getStorageDirectory(): string
    {
        return match ($this) {
            self::SoalBerkas => 'soal-berkas',
            self::Paspor => 'paspor',
            self::IjinDesa => 'ijin-desa',
            self::Rekomendasi => 'rekomendasi',
            self::WorkingPermit => 'working-permit',
            self::VisaDocument => 'visa',
            self::MedicalFullReport => 'medical-full',
            self::OPPDocument => 'opp',
        };
    }
}
