<?php

use App\Services\YoutubeIdExtractionService;

describe('valid hosts', function () {
    it('extracts id from valid youtube hosts', function (string $url, string $expectedId) {
        $result = YoutubeIdExtractionService::from(url: $url)->get();

        expect($result)->toBe($expectedId);
    })->with([
        'youtube.com' => ['https://youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'www.youtube.com' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'm.youtube.com' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'music.youtube.com' => ['https://music.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'www.youtu.be' => ['https://www.youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'youtube-nocookie.com' => ['https://youtube-nocookie.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
        'www.youtube-nocookie.com' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ]);
});

describe('invalid hosts', function () {
    it('returns null for invalid hosts', function (string $url) {
        $result = YoutubeIdExtractionService::from(url: $url)->get();

        expect($result)->toBeNull();
    })->with([
        'vimeo.com' => ['https://vimeo.com/123456789'],
        'dailymotion.com' => ['https://dailymotion.com/video/x123456'],
        'fake-youtube.com' => ['https://fake-youtube.com/watch?v=dQw4w9WgXcQ'],
        'youtubecom.com' => ['https://youtubecom.com/watch?v=dQw4w9WgXcQ'],
        'notyoutube.be' => ['https://notyoutube.be/dQw4w9WgXcQ'],
        'empty string' => [''],
        'invalid url' => ['not-a-url'],
    ]);
});

describe('url patterns', function () {
    it('extracts id from standard watch url', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from watch url with additional parameters', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=120&list=PLtest')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from youtu.be short url', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://youtu.be/dQw4w9WgXcQ')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from youtu.be short url with parameters', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://youtu.be/dQw4w9WgXcQ?t=120')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from embed url', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://www.youtube.com/embed/dQw4w9WgXcQ')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from legacy v url', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://www.youtube.com/v/dQw4w9WgXcQ')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from shorts url', function () {
        $result = YoutubeIdExtractionService::from(url: 'https://www.youtube.com/shorts/dQw4w9WgXcQ')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });

    it('extracts id from http url', function () {
        $result = YoutubeIdExtractionService::from(url: 'http://www.youtube.com/watch?v=dQw4w9WgXcQ')->get();

        expect($result)->toBe('dQw4w9WgXcQ');
    });
});

describe('video id validation', function () {
    it('accepts valid 11 character ids', function (string $url, string $expectedId) {
        $result = YoutubeIdExtractionService::from(url: $url)->get();

        expect($result)->toBe($expectedId);
    })->with([
        'alphanumeric' => ['https://youtu.be/abc123XYZ90', 'abc123XYZ90'],
        'with hyphens' => ['https://youtu.be/abc-123-XYZ', 'abc-123-XYZ'],
        'with underscores' => ['https://youtu.be/abc_123_XYZ', 'abc_123_XYZ'],
        'mixed special' => ['https://youtu.be/a-b_c-d_e-f', 'a-b_c-d_e-f'],
    ]);

    it('returns null for invalid video ids', function (string $url) {
        $result = YoutubeIdExtractionService::from(url: $url)->get();

        expect($result)->toBeNull();
    })->with([
        'too short' => ['https://youtu.be/abc123'],
        'too long' => ['https://youtu.be/abc123XYZ90extra'],
        'invalid characters' => ['https://youtu.be/abc123XYZ!@'],
        'empty path' => ['https://youtu.be/'],
        'missing v parameter' => ['https://www.youtube.com/watch?list=PLtest'],
    ]);
});
