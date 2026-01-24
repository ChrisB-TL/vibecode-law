<?php

namespace App\Concerns;

use App\ValueObjects\FrontendEnum;
use BackedEnum;

trait FrontendTransformable
{
    public function forFrontend(): FrontendEnum
    {
        $isBacked = $this instanceof BackedEnum;

        return new FrontendEnum(
            value: $isBacked ? $this->value : $this->name,
            label: $this->label(),
            name: $isBacked ? $this->name : null,
        );
    }

    abstract public function label(): string;
}
