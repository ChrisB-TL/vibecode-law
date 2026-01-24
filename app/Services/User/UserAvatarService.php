<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAvatarService
{
    public function __construct(
        protected User $user
    ) {}

    /**
     * Store an avatar from an uploaded file.
     */
    public function fromUploadedFile(UploadedFile $file): void
    {
        $extension = $file->extension();

        $this->storeAvatar(
            content: $file->getContent(),
            extension: $extension
        );

        $this->user->save();
    }

    /**
     * Store an avatar from raw content (e.g., from an external URL).
     */
    public function fromContent(string $content, string $mimeType): void
    {
        $extension = imageMimeToExtension($mimeType);

        if ($extension === null) {
            return;
        }

        $this->storeAvatar(
            content: $content,
            extension: $extension
        );
    }

    protected function storeAvatar(string $content, string $extension): void
    {
        $path = 'users/avatars/'.Str::uuid()->toString().'.'.$extension;

        if ($this->user->avatar_path !== null) {
            Storage::disk('public')->delete($this->user->avatar_path);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $content
        );

        $this->user->avatar_path = $path;
    }

    /**
     * Delete the user's avatar.
     */
    public function delete(): void
    {
        if ($this->user->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->user->avatar_path);

        $this->user->avatar_path = null;
        $this->user->save();
    }
}
