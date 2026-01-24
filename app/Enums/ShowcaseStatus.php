<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum ShowcaseStatus: int
{
    use FrontendTransformable;

    case Draft = 1;
    case Pending = 2;
    case Approved = 3;
    case Rejected = 4;

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
