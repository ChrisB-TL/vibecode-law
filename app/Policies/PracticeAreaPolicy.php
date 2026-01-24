<?php

namespace App\Policies;

use App\Models\PracticeArea;
use App\Models\User;

class PracticeAreaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, PracticeArea $practiceArea): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('practice-area.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PracticeArea $practiceArea): bool
    {
        return $user->can('practice-area.update');
    }
}
