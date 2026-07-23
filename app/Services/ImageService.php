<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Compress and store user-uploaded images with privacy-safe defaults.
 *
 * Used by onboarding photo uploads and selfie verification. All images are:
 *  - resized to a configurable max width
 *  - re-encoded as JPEG with configurable quality
 *  - stored on the configured disk (default: public)
 *  - saved with a random filename and original size recorded for quota display
 */
class ImageService
{
    private int $maxWidth;

    private int $quality;

    private string $disk;

    public function __construct()
    {
        $this->maxWidth = (int) config('matchlock.image_max_width', 1200);
        $this->quality = (int) config('matchlock.image_quality', 75);
        $this->disk = (string) config('matchlock.image_disk', 'public');
    }

    /**
     * Compress an uploaded image and return the stored relative path.
     *
     * @param UploadedFile $file
     * @param string       $directory  Storage directory (e.g. 'photos' or 'selfies')
     * @param string|null  $filename   Optional base filename (without extension); random if null
     *
     * @throws RuntimeException|FileException
     */
    public function store(UploadedFile $file, string $directory = 'photos', ?string $filename = null): string
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Invalid image upload.');
        }

        $filename = ($filename ?: uniqid('', true)) . '.jpg';
        $path = trim($directory, '/') . '/' . $filename;

        $image = Image::read($file->getRealPath());

        $image = $this->resize($image);
        $image = $image->encodeByExtension('jpg', quality: $this->quality);

        $stored = Storage::disk($this->disk)->put($path, $image->toString());

        if (! $stored) {
            throw new RuntimeException('Failed to store image.');
        }

        return $path;
    }

    /**
     * Return a public URL for a stored image path.
     */
    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Delete an image from storage.
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get the original file size in KB before compression.
     */
    public function originalSizeKb(UploadedFile $file): int
    {
        return (int) round($file->getSize() / 1024);
    }

    private function resize(InterventionImage $image): InterventionImage
    {
        $width = $image->width();

        if ($width <= $this->maxWidth) {
            return $image;
        }

        return $image->scaleDown(width: $this->maxWidth);
    }
}
