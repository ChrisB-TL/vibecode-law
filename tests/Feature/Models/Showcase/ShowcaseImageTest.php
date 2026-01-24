<?php

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('deletes file from storage when model is deleted', function () {
    Storage::fake('public');

    $showcase = Showcase::factory()->create();
    $image = ShowcaseImage::factory()->for($showcase)->create([
        'path' => 'showcase/123/images/test-image.jpg',
    ]);

    Storage::disk('public')->put($image->path, 'test content');
    Storage::disk('public')->assertExists($image->path);

    $image->delete();

    Storage::disk('public')->assertMissing('showcase/123/images/test-image.jpg');
});

test('deletes file from storage when deleted via relationship', function () {
    Storage::fake('public');

    $showcase = Showcase::factory()->create();
    $image = ShowcaseImage::factory()->for($showcase)->create([
        'path' => 'showcase/789/images/relationship-delete.jpg',
    ]);

    Storage::disk('public')->put($image->path, 'test content');
    Storage::disk('public')->assertExists($image->path);

    // Delete via relationship using model method (triggers events)
    $showcase->images()->where('id', $image->id)->get()->each->delete();

    Storage::disk('public')->assertMissing('showcase/789/images/relationship-delete.jpg');
});

describe('url attribute', function () {
    test('url returns storage url when image transform base url is not set', function () {
        Storage::fake('public');
        Config::set('services.image-transform.base_url', null);

        $showcaseImage = ShowcaseImage::factory()->make(['path' => 'showcase/1/images/test-image.jpg']);

        expect($showcaseImage->url)->toBe(Storage::disk('public')->url('showcase/1/images/test-image.jpg'));
    });

    test('url returns image transform url when image transform base url is set', function () {
        Config::set('services.image-transform.base_url', 'https://images.example.com');

        $showcaseImage = ShowcaseImage::factory()->make(['path' => 'showcase/1/images/test-image.jpg']);

        expect($showcaseImage->url)->toBe('https://images.example.com/showcase/1/images/test-image.jpg');
    });
});
