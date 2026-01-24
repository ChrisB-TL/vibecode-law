<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Enums\TeamType;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class CreateController extends BaseController
{
    public function __invoke(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('staff-area/users/create', [
            'roles' => $this->getRoles(),
            'teamTypes' => $this->getTeamTypes(),
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function getRoles(): Collection
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name');
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function getTeamTypes(): array
    {
        return array_map(
            fn (TeamType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            TeamType::cases()
        );
    }
}
