<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Actions\User\SendPasswordResetAction;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;

class SendPasswordResetController extends BaseController
{
    public function __invoke(User $user, SendPasswordResetAction $action): RedirectResponse
    {
        $this->authorize('sendPasswordReset', $user);

        $status = $action->send(user: $user);

        return $this->buildResponse(status: $status);
    }

    private function buildResponse(string $status): RedirectResponse
    {
        if ($status === Password::RESET_LINK_SENT) {
            return Redirect::back()
                ->with('flash', [
                    'message' => ['message' => 'Password reset email sent successfully.', 'type' => 'success'],
                ]);
        }

        return Redirect::back()
            ->with('flash', [
                'message' => ['message' => 'Failed to send password reset email.', 'type' => 'error'],
            ]);
    }
}
