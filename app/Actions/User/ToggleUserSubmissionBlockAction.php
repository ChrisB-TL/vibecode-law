<?php

namespace App\Actions\User;

use App\Models\User;

class ToggleUserSubmissionBlockAction
{
    public function toggle(User $user): void
    {
        $user->update([
            'blocked_from_submissions_at' => $user->blocked_from_submissions_at === null
                ? now()
                : null,
        ]);
    }
}
