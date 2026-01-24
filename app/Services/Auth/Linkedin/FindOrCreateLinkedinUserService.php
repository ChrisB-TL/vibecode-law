<?php

namespace App\Services\Auth\Linkedin;

use App\Actions\User\GenerateUniqueUserHandleAction;
use App\Models\User;
use Laravel\Socialite\Two\User as LinkedinUser;

class FindOrCreateLinkedinUserService
{
    public function __construct(
        protected LinkedinUser $linkedinUser,
        protected GenerateUniqueUserHandleAction $handleAction = new GenerateUniqueUserHandleAction,
    ) {}

    /**
     * @return User|string Returns an error message string on failure.
     */
    public function handle(): User|string
    {
        return $this->findByLinkedinId()
            ?? $this->findOrCreateByEmail();
    }

    protected function findByLinkedinId(): ?User
    {
        $user = User::query()
            ->where('linkedin_id', '=', $this->linkedinUser->id)
            ->first();

        if ($user === null) {
            return null;
        }

        return $this->updateExistingUser(
            user: $user,
            linkProfiles: false
        );
    }

    protected function findOrCreateByEmail(): User|string
    {
        $user = User::query()
            ->where('email', '=', $this->linkedinUser->email)
            ->first();

        if ($user === null) {
            return $this->createNewUser();
        }

        if ($user->email_verified_at === null) {
            $linkedIsVerified = $this->linkedinUser->user['email_verified'] ?? false;

            if ($linkedIsVerified === false) {
                return 'Your Linkedin account does not have a verified email address. Please verify it and try again.';
            }

            $user->email_verified_at = now();
        }

        return $this->updateExistingUser(
            user: $user,
            linkProfiles: true
        );
    }

    protected function updateExistingUser(User $user, bool $linkProfiles): User
    {
        User::unguard();

        $user->fill(array_filter([
            'first_name' => $this->linkedinUser->user['given_name'],
            'last_name' => $this->linkedinUser->user['family_name'],
            'email' => $this->linkedinUser->email,
            'linkedin_token' => $this->linkedinUser->token,
            'linkedin_id' => $linkProfiles ? $this->linkedinUser->id : null,
        ]));

        $user->save();

        User::reguard();

        return $user;
    }

    protected function createNewUser(): User|string
    {
        $linkedIsVerified = $this->linkedinUser->user['email_verified'] ?? false;

        if ($linkedIsVerified !== true) {
            return 'Your LinkedIn email address has not been verified. Please verify your email on LinkedIn and try again.';
        }

        $firstName = $this->linkedinUser->user['given_name'];
        $lastName = $this->linkedinUser->user['family_name'];

        User::unguard();

        $user = User::create([
            'linkedin_id' => $this->linkedinUser->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'handle' => $this->handleAction->generate(
                firstName: $firstName,
                lastName: $lastName,
            ),
            'email' => $this->linkedinUser->email,
            'linkedin_token' => $this->linkedinUser->token,
            'email_verified_at' => now(),
        ]);

        User::reguard();

        return $user;
    }
}
