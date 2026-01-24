<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\UserUpdateRequest;
use App\Models\User;
use App\Services\User\UserAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update($request->safe()->except(['roles', 'avatar', 'remove_avatar']));

        $this->handleAvatar(request: $request, user: $user);
        $this->syncRoles(request: $request, user: $user);

        return Redirect::route('staff.users.edit', $user)
            ->with('flash', [
                'message' => ['message' => 'User updated successfully.', 'type' => 'success'],
            ]);
    }

    private function handleAvatar(UserUpdateRequest $request, User $user): void
    {
        $avatarService = new UserAvatarService(user: $user);

        if ($request->boolean('remove_avatar') === true) {
            $avatarService->delete();

            return;
        }

        if ($request->hasFile('avatar') === true) {
            $avatarService->fromUploadedFile(file: $request->file('avatar'));
        }
    }

    private function syncRoles(UserUpdateRequest $request, User $user): void
    {
        if ($request->has('roles') === false) {
            return;
        }

        $user->syncRoles($request->validated('roles') ?? []);
    }
}
