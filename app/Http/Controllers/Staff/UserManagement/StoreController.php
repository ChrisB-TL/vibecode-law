<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Actions\Staff\User\InviteUserAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\UserStoreRequest;
use App\Models\User;
use App\Services\User\UserAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(UserStoreRequest $request, InviteUserAction $inviteAction): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $inviteAction->invite(
            data: $request->safe()->except(['roles', 'avatar']),
            roles: $request->validated('roles') ?? [],
        );

        $this->handleAvatar(request: $request, user: $user);

        return Redirect::route('staff.users.edit', $user)
            ->with('flash', [
                'message' => ['message' => 'User created and invitation sent.', 'type' => 'success'],
            ]);
    }

    private function handleAvatar(UserStoreRequest $request, User $user): void
    {
        if ($request->hasFile('avatar') === false) {
            return;
        }

        $avatarService = new UserAvatarService(user: $user);
        $avatarService->fromUploadedFile(file: $request->file('avatar'));
    }
}
