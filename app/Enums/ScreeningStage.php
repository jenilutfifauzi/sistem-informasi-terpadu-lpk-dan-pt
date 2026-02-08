<?php

namespace App\Enums;

enum ScreeningStage: string
{
    case Screening1 = 'Screening 1';
    case InterviewUser = 'Interview User';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getStageNumber(): int
    {
        return match ($this) {
            self::Screening1 => 6,
            self::InterviewUser => 7,
        };
    }
}
