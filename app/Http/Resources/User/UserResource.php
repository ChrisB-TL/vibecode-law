<?php

namespace App\Http\Resources\User;

use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UserResource extends Resource
{
    public Lazy|int $id;

    public string $first_name;

    public string $last_name;

    public string $handle;

    public ?string $organisation;

    public ?string $job_title;

    public ?string $avatar;

    public ?string $linkedin_url;

    public ?string $team_role;

    public Lazy|null|string $bio;

    public Lazy|null|string $bio_html;

    public static function fromModel(User $user): self
    {
        $markdown = app(abstract: MarkdownService::class);

        return self::from([
            'id' => Lazy::create(fn () => $user->id),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'organisation' => $user->organisation,
            'job_title' => $user->job_title,
            'avatar' => $user->avatar,
            'linkedin_url' => $user->linkedin_url,
            'team_role' => $user->team_role,
            'bio' => Lazy::create(fn () => $user->bio),
            'bio_html' => Lazy::create(fn () => $user->bio !== null ? $markdown->render(
                markdown: $user->bio,
                cacheKey: "user|{$user->id}|bio",
            ) : null),
        ]);
    }
}
