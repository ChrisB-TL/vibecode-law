<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum SourceStatus: int
{
    use FrontendTransformable;

    case NotAvailable = 1;
    case SourceAvailable = 2;
    case OpenSource = 3;

    public function label(): string
    {
        return match ($this) {
            self::NotAvailable => 'Not Available',
            self::SourceAvailable => 'Source Available',
            self::OpenSource => 'Open Source',
        };
    }

    public function hasSourceUrl(): bool
    {
        return $this !== self::NotAvailable;
    }
}
