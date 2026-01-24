<?php

namespace Database\Factories\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasStockImages
{
    protected Collection $existingImages;

    protected function getRandomStockImagePath(): string
    {
        return $this->getImagePool()->shuffle()->pop();
    }

    protected function getImagePool(): Collection
    {
        if (isset($this->existingImages)) {
            return $this->existingImages;
        }

        $this->existingImages = new Collection(Storage::allFiles('product-image-pool'));

        $existingImagesCount = $this->existingImages->count();

        if ($existingImagesCount >= 50) {
            return $this->existingImages;
        }

        for ($i = $existingImagesCount; $i < 50; $i++) {
            $this->addToImagePool();
        }

        return $this->existingImages = new Collection(Storage::allFiles('product-image-pool'));
    }

    protected function addToImagePool(): void
    {
        $imageResponse = Http::get('https://picsum.photos/800/450');

        $mimeType = $imageResponse->header('Content-Type');
        $extension = imageMimeToExtension($mimeType);

        if ($extension === null) {
            return;
        }

        $filename = Str::uuid()->toString().'.'.$extension;

        Storage::put('product-image-pool/'.$filename, $imageResponse->body());
    }
}
