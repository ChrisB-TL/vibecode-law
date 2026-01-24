<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Actions\User\ToggleUserSubmissionBlockAction;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ToggleSubmissionsController extends BaseController
{
    public function __invoke(User $user, ToggleUserSubmissionBlockAction $action): RedirectResponse
    {
        $this->authorize('toggleSubmissions', $user);

        $action->toggle(user: $user);

        return Redirect::back()
            ->with('flash', [
                'message' => ['message' => $this->getFlashMessage(user: $user), 'type' => 'success'],
            ]);
    }

    private function getFlashMessage(User $user): string
    {
        return $user->blocked_from_submissions_at !== null
            ? 'User has been blocked from submissions.'
            : 'User has been unblocked from submissions.';
    }
}
