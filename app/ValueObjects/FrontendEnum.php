<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @implements Arrayable<string, string|null>
 */
#[TypeScript]
class FrontendEnum implements Arrayable
{
    public function __construct(
        public readonly string $value,
        public readonly string $label,
        public readonly ?string $name = null,
    ) {}

    /**
     * @return array{value: string, label: string, name?: string}
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'value' => $this->value,
            'label' => $this->label,
        ]);
    }
}
