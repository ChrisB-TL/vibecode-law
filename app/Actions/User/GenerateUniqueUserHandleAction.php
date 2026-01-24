<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Str;

class GenerateUniqueUserHandleAction
{
    public function generate(string $firstName, string $lastName): string
    {
        $baseHandle = Str::slug(title: $firstName.' '.$lastName);

        if ($this->isUnique(handle: $baseHandle)) {
            return $baseHandle;
        }

        return $this->generateWithSuffix(baseHandle: $baseHandle);
    }

    protected function isUnique(string $handle): bool
    {
        return User::query()
            ->where('handle', '=', $handle)
            ->doesntExist();
    }

    protected function generateWithSuffix(string $baseHandle): string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $suffix = random_int(min: 100000, max: 999999);
            $handle = $baseHandle.'-'.$suffix;

            if ($this->isUnique(handle: $handle)) {
                return $handle;
            }
        }

        // Fallback to a UUID-based handle if we can't find a unique one
        return $baseHandle.'-'.Str::random(length: 8);
    }
}
