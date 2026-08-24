<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Format;
use RuntimeException;
use Throwable;

class ImageService
{
    /**
     * Process, resize, and store an event cover image.
     *
     * @throws RuntimeException
     */
    public function storeCoverImage(UploadedFile $file, ?string $oldImagePath = null): string
    {
        try {
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($file->getRealPath());
            $image->scaleDown(width: 1920, height: 1080);
            $imageData = $image->encodeUsingFormat(Format::WEBP, quality: 65);

            $filename = Str::uuid() . '.webp';
            $path = 'events/' . $filename;

            $stored = Storage::disk('public')->put($path, (string) $imageData);

            if (!$stored) {
                throw new RuntimeException("Failed to write image stream to disk at path: public/{$path}");
            }
            if (!Storage::disk('public')->exists($path)) {
                throw new RuntimeException("Image write reported success, but file was not found on disk: public/{$path}");
            }

            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return $path;

        } catch (Throwable $e) {
            throw new RuntimeException("ImageService failed to process cover image: " . $e->getMessage(), 0,$e);
        }
    }
}