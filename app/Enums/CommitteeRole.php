<?php

namespace App\Enums;

enum CommitteeRole: string
{
    case President = 'president';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::President => 'Predsednik komisije',
            self::Member => 'Član komisije',
        };
    }
}
