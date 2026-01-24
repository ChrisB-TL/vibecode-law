<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class SendPasswordResetAction
{
    public function send(User $user): string
    {
        return Password::broker()->sendResetLink(['email' => $user->email]);
    }
}
