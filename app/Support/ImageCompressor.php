<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

use Intervention\Image\Drivers\Imagick\Driver;

use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;

class ImageCompressor
{
    public static function run(
        string $relativePath = 'posts',
        string $disk = 'public',
        int $maxWidth = 1080,
        int $quality = 82,
        bool $makeWebp = true
    ): void {
        $full = Storage::disk($disk)->path($relativePath);

        $manager = new ImageManager(new Driver()); // Imagick Driver
        $img = $manager->read($full);

        // Resize kalau lebih lebar dari maxWidth
        if ($maxWidth > 0 && $img->width() > $maxWidth) {
            $img->scaleDown($maxWidth);
        }

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));

        // Re-encode ke format aslinya
        if (in_array($ext, ['jpg', 'jpeg'])) {
            $img->encode(new JpegEncoder(quality: $quality));
            $img->save($full); // simpan sebagai JPG terkompres
        } elseif ($ext === 'png') {

            $img->encode(new PngEncoder());
            $img->save($full);
        } else {
            // Format lain: simpan ulang saja
            $img->save($full);
        }

        // Buat versi WebP
        if ($makeWebp && in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $full);
            $encoded = $img->encode(new WebpEncoder(quality: $quality)); // return EncodedImage
            // simpan binary hasil encode
            file_put_contents($webpPath, (string) $encoded);
        }
    }
}
