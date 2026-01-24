<?php

namespace App\Services\Auth\Linkedin;

use App\Models\User;
use App\Services\User\UserAvatarService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Two\User as LinkedinUser;

class SyncUserLinkedinService
{
    public function __construct(
        protected LinkedinUser $linkedinUser,
        protected User $localUser
    ) {}

    public function handle(): void
    {
        $this->handleAvatar();

        $this->handleProfileUrl();

        if ($this->localUser->isDirty(['avatar_path', 'linkedin_url'])) {
            $this->localUser->save();
        }
    }

    protected function handleAvatar(): void
    {
        if ($this->linkedinUser->getAvatar() === null) {
            return;
        }

        /** @var Response */
        $avatarResponse = Http::get($this->linkedinUser->getAvatar());

        if ($avatarResponse->ok() === false) {
            return;
        }

        $mimeType = $avatarResponse->header('Content-Type');

        if (imageMimeToExtension($mimeType) === null) {
            return;
        }

        $content = $avatarResponse->body();

        if ($this->hasAvatarChanged(newContent: $content) === false) {
            return;
        }

        new UserAvatarService(user: $this->localUser)->fromContent(
            content: $content,
            mimeType: $mimeType
        );
    }

    protected function hasAvatarChanged(string $newContent): bool
    {
        if ($this->localUser->avatar_path === null) {
            return true;
        }

        $currentAvatar = Storage::disk('public')->get($this->localUser->avatar_path);

        return crc32($currentAvatar) !== crc32($newContent);
    }

    protected function handleProfileUrl(): void
    {
        if (Config::get('services.linkedin-openid.auto_fetch_profile_url') === false) {
            return;
        }

        if ($this->localUser->linkedin_url !== null) {
            return;
        }

        /** @var Response */
        $response = Http::withToken($this->localUser->linkedin_token)
            ->acceptJson()
            ->withHeader('LinkedIn-Version', '202510.03')
            ->get('https://api.linkedin.com/rest/identityMe');

        if ($response->ok() === false) {
            return;
        }

        $profileRedirect = json_decode($response->body(), true)['basicInfo']['profileUrl'] ?? null;

        if ($profileRedirect === null) {
            return;
        }

        /** @var Response */
        $followProfileResponse = Http::withoutRedirecting()
            ->get($profileRedirect);

        if ($followProfileResponse->redirect() === false) {
            return;
        }

        $linkedinUrl = $followProfileResponse->header('Location');

        if ($linkedinUrl !== '') {
            $this->localUser->linkedin_url = $linkedinUrl;
        }
    }
}
