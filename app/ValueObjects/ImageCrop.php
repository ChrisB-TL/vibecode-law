<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @implements Arrayable<string, int>
 */
#[TypeScript]
class ImageCrop implements Arrayable
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly int $width,
        public readonly int $height,
    ) {}

    /**
     * @param  array{x: int, y: int, width: int, height: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            x: $data['x'],
            y: $data['y'],
            width: $data['width'],
            height: $data['height'],
        );
    }

    /**
     * @return array{x: int, y: int, width: int, height: int}
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
