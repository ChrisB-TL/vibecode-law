<?php

namespace App\Actions\User;

use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteUserAction
{
    public function delete(User $user): void
    {
        // Delete avatar from storage if it exists
        if ($user->avatar_path !== null) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Nullify user_id on showcases to keep them orphaned
        Showcase::query()
            ->where('user_id', $user->id)
            ->update(['user_id' => null]);

        // Delete the user record
        $user->delete();
    }
}
