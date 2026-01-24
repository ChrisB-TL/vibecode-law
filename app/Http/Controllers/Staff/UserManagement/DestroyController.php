<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Actions\User\DeleteUserAction;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class DestroyController extends BaseController
{
    public function __invoke(User $user, DeleteUserAction $action): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            abort(403, 'You cannot delete your own account.');
        }

        $this->authorize('delete', $user);

        $action->delete(user: $user);

        return Redirect::route('staff.users.index')
            ->with('flash', [
                'message' => ['message' => 'User deleted successfully.', 'type' => 'success'],
            ]);
    }
}
