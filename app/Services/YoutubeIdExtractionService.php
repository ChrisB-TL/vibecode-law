<?php

namespace App\Services;

use Illuminate\Support\Uri;

class YoutubeIdExtractionService
{
    protected Uri $uri;

    public function __construct(protected string $url) {}

    public static function from(string $url): self
    {
        return new self($url);
    }

    public function get(): ?string
    {
        if ($this->validate() === false) {
            return null;
        }

        return $this->extractId();
    }

    protected function validate(): bool
    {
        if ($this->makeUri() === false) {
            return false;
        }

        if ($this->checkHost() === false) {
            return false;
        }

        return true;
    }

    protected function makeUri(): bool
    {
        try {
            $this->uri = Uri::of(uri: $this->url);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    protected function checkHost(): bool
    {
        $host = strtolower($this->uri->host() ?? '');

        if ($host === '') {
            return false;
        }

        $validHosts = [
            'youtube.com',
            'www.youtube.com',
            'm.youtube.com',
            'music.youtube.com',
            'youtu.be',
            'www.youtu.be',
            'youtube-nocookie.com',
            'www.youtube-nocookie.com',
        ];

        return in_array(needle: $host, haystack: $validHosts, strict: true);
    }

    protected function extractId(): ?string
    {
        $host = strtolower($this->uri->host() ?? '');
        $path = $this->uri->path();

        // youtu.be short URLs: youtu.be/VIDEO_ID
        if (str_contains(haystack: $host, needle: 'youtu.be')) {
            return $this->validateId(id: $path);
        }

        // Standard watch URLs: youtube.com/watch?v=VIDEO_ID
        $videoId = $this->uri->query()->get(key: 'v');
        if ($videoId !== null) {
            return $this->validateId(id: $videoId);
        }

        // Path-based patterns: embed/, v/, shorts/
        $patterns = [
            '#^embed/([a-zA-Z0-9_-]{11})#',
            '#^v/([a-zA-Z0-9_-]{11})#',
            '#^shorts/([a-zA-Z0-9_-]{11})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match(pattern: $pattern, subject: $path, matches: $matches) === 1) {
                return $this->validateId(id: $matches[1]);
            }
        }

        return null;
    }

    protected function validateId(string $id): ?string
    {
        // 11 characters: alphanumeric, hyphens, underscores
        if (preg_match(pattern: '/^[a-zA-Z0-9_-]{11}$/', subject: $id) === 1) {
            return $id;
        }

        return null;
    }
}
