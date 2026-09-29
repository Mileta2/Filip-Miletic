<?php

namespace App\Enums;

enum TopicType: string
{
    case Undergraduate = 'diplomski';
    case Master = 'master';

    public function label(): string
    {
        return match ($this) {
            self::Undergraduate => 'Diplomski rad',
            self::Master => 'Master rad',
        };
    }
}
