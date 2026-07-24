<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Server-side compression/resizing for uploaded photos (§8.5): resize to a
 * max width on the longest edge, compress to a configurable JPEG quality.
 * Data-cost-conscious by design for the target Ghana market (§13.3).
 */
final class ImageService
{
    public function compressAndStore(UploadedFile $file, string $directory): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());

        $maxWidth = (int) config('matchlock.image_max_width', 1200);
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $quality = (int) config('matchlock.image_quality', 75);
        $encoded = $image->toJpeg($quality);

        $filename = Str::uuid()->toString().'.jpg';
        $path = trim($directory, '/').'/'.$filename;

        Storage::disk(config('matchlock.image_disk', 'public'))->put($path, (string) $encoded);

        return $path;
    }
}
