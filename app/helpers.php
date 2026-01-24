<?php

if (! function_exists('imageMimeToExtension')) {
    function imageMimeToExtension(string $mimeType): ?string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null
        };
    }
}
