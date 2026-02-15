<?php

namespace App\Enums;

enum CTKStatus: string
{
    case MCU = 'MCU';
    case Pembayaran = 'Pembayaran';
    case SoalBerkas = 'Soal/Berkas';
    case Paspor = 'Paspor';
    case BelajarDiLPK = 'Belajar di LPK';
    case Screening1 = 'Screening 1';
    case InterviewUser = 'Interview User';
    case IjinDesa = 'Ijin Desa';
    case Rekomendasi = 'Rekomendasi';
    case WP = 'WP';
    case ApplyVisa = 'Apply Visa';
    case MedicalFull = 'Medical Full';
    case Visa = 'Visa';
    case OPP = 'OPP';
    case Terbang = 'Terbang';

    public function getStageNumber(): int
    {
        return match ($this) {
            self::MCU => 1,
            self::Pembayaran => 2,
            self::SoalBerkas => 3,
            self::Paspor => 4,
            self::BelajarDiLPK => 5,
            self::Screening1 => 6,
            self::InterviewUser => 7,
            self::IjinDesa => 8,
            self::Rekomendasi => 9,
            self::WP => 10,
            self::ApplyVisa => 11,
            self::MedicalFull => 12,
            self::Visa => 13,
            self::OPP => 14,
            self::Terbang => 15,
        };
    }

    public function getLabel(): string
    {
        return $this->value;
    }

    public static function fromStageNumber(int $stage): ?self
    {
        return match ($stage) {
            1 => self::MCU,
            2 => self::Pembayaran,
            3 => self::SoalBerkas,
            4 => self::Paspor,
            5 => self::BelajarDiLPK,
            6 => self::Screening1,
            7 => self::InterviewUser,
            8 => self::IjinDesa,
            9 => self::Rekomendasi,
            10 => self::WP,
            11 => self::ApplyVisa,
            12 => self::MedicalFull,
            13 => self::Visa,
            14 => self::OPP,
            15 => self::Terbang,
            default => null,
        };
    }
}
