<?php

namespace App\Enums;

enum TeamType: int
{
    case CoreTeam = 1;
    case Collaborator = 2;

    public function label(): string
    {
        return match ($this) {
            self::CoreTeam => 'Core Team',
            self::Collaborator => 'Collaborator',
        };
    }
}
