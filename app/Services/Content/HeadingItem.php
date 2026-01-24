<?php

namespace App\Services\Content;

final readonly class HeadingItem
{
    /**
     * @param  array<HeadingItem>  $children
     */
    public function __construct(
        public string $text,
        public string $slug,
        public int $level,
        public array $children = []
    ) {}
}
