<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum ShowcaseDraftStatus: int
{
    use FrontendTransformable;

    case Draft = 1;
    case Pending = 2;
    case Rejected = 3;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
        };
    }
}
