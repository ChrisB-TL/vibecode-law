<?php

namespace App\Http\Controllers\About;

use App\Http\Controllers\BaseController;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends BaseController
{
    public function __invoke(): Response
    {
        return Inertia::render(component: 'about/community', props: [
            'title' => 'The Community',
            'coreTeam' => UserResource::collect($this->coreTeam()),
            'collaborators' => UserResource::collect($this->collaborators()),
        ]);
    }

    private function coreTeam(): Collection
    {
        return User::query()
            ->coreTeam()
            ->orderBy('team_order')
            ->orderBy('first_name')
            ->get();
    }

    private function collaborators(): Collection
    {
        return User::query()
            ->collaborators()
            ->orderBy('team_order')
            ->orderBy('first_name')
            ->get();
    }
}
