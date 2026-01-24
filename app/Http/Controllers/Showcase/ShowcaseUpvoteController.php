<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\BaseController;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ShowcaseUpvoteController extends BaseController
{
    public function __invoke(Request $request, Showcase $showcase): RedirectResponse
    {
        if ($showcase->isApproved() === false) {
            abort(404);
        }

        /** @var User $user */
        $user = $request->user();

        if ($this->hasUpvoted(showcase: $showcase, user: $user)) {
            return $this->removeVote(showcase: $showcase, user: $user);
        }

        return $this->addVote(showcase: $showcase, user: $user);
    }

    private function hasUpvoted(Showcase $showcase, User $user): bool
    {
        return $showcase->upvoters()->where('user_id', $user->id)->exists();
    }

    private function removeVote(Showcase $showcase, User $user): RedirectResponse
    {
        $showcase->upvoters()->detach($user->id);

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Upvote removed.', 'type' => 'success'],
        ]);
    }

    private function addVote(Showcase $showcase, User $user): RedirectResponse
    {
        $showcase->upvoters()->attach($user->id);

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Showcase upvoted.', 'type' => 'success'],
        ]);
    }
}
