<?php

namespace App\Http\Resources;

use App\Models\PracticeArea;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PracticeAreaResource extends Resource
{
    public int $id;

    public string $name;

    public string $slug;

    public Lazy|int $showcases_count;

    public static function fromModel(PracticeArea $practiceArea): self
    {
        return self::from([
            'id' => $practiceArea->id,
            'name' => $practiceArea->name,
            'slug' => $practiceArea->slug,
            'showcases_count' => Lazy::when(
                condition: fn () => $practiceArea->hasAttribute('showcases_count'),
                value: fn () => $practiceArea->showcases_count,
            ),
        ]);
    }
}
