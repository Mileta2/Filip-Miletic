<?php

namespace App\Enums;

enum StudyLevel: string
{
    case Undergraduate = 'diplomski';
    case Master = 'master';

    public function label(): string
    {
        return match ($this) {
            self::Undergraduate => 'Osnovne studije',
            self::Master => 'Master studije',
        };
    }
}
