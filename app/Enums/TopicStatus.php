<?php

namespace App\Enums;

enum TopicStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Defended = 'defended';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Slobodna',
            self::Reserved => 'Zauzeta',
            self::Defended => 'Odbranjena',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'text-bg-success',
            self::Reserved => 'text-bg-warning',
            self::Defended => 'text-bg-primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Available => 'bi-unlock-fill',
            self::Reserved => 'bi-lock-fill',
            self::Defended => 'bi-check-circle-fill',
        };
    }

    public function cardClass(): string
    {
        return 'topic-card-'.$this->value;
    }
}
