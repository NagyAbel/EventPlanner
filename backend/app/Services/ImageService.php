<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Format;

class ImageService
{
    /**
     * Process, resize, and store an event cover image.
     */
    public function storeCoverImage(UploadedFile $file, ?string $oldImagePath = null): string
    {
        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decodePath($file->getRealPath());
        $image->scaleDown(width: 1920, height: 1080);
        $imageData = $image->encodeUsingFormat(Format::WEBP, quality: 65);

        $filename = Str::uuid() . '.webp';
        $path = 'events/' . $filename;

        Storage::disk('public')->put($path, (string) $imageData);

        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return $path;
    }
}