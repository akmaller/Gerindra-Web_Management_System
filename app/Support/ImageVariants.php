<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
// Pilih driver sesuai yang kamu pakai; Imagick direkomendasikan
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;

class ImageVariants
{
    /**
     * Generate multiple sizes (thumbnail, small, middle) + WebP untuk masing-masing.
     *
     * @param string $relativePath  Path relatif di disk (mis: "uploads/posts/foo.jpg")
     * @param string $disk          Disk Laravel (default 'public')
     * @param array  $sizes         ['thumb'=>360, 'small'=>720, 'middle'=>1200]
     * @param int    $quality       Kualitas JPG/WEBP (1-100)
     */
    public static function generate(
        string $relativePath,
        string $disk = 'public',
        array $sizes = ['thumb' => 1280, 'small' => 200, 'middle' => 400],
        int $quality = 82
    ): void {
        $full = Storage::disk($disk)->path($relativePath);
        if (!file_exists($full)) {
            return;
        }

        $manager = new ImageManager(new Driver());
        $img = $manager->read($full);

        // Kompres ulang original secukupnya
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg'])) {
            $img->encode(new JpegEncoder(quality: $quality))->save($full);
        } elseif ($ext === 'png') {
            $img->encode(new PngEncoder())->save($full);
        } else {
            $img->save($full);
        }

        // Variant maker
        foreach ($sizes as $suffix => $targetW) {
            self::makeVariant($img, $full, $ext, $suffix, (int) $targetW, $quality);
        }
    }

    protected static function makeVariant($img, string $full, string $ext, string $suffix, int $targetW, int $quality): void
    {
        // Clone dari original agar tiap varian bersih
        $variant = clone $img;

        // Resize jika lebih besar dari target
        if ($variant->width() > $targetW) {
            $variant->scaleDown($targetW);
        }

        // Path dasar
        $dir = dirname($full);
        $base = pathinfo($full, PATHINFO_FILENAME); // tanpa ext

        // Simpan fallback JPEG/PNG sesuai asli
        if (in_array($ext, ['jpg', 'jpeg'])) {
            $pathFallback = $dir . DIRECTORY_SEPARATOR . "{$base}-{$suffix}.jpg";
            $variant->encode(new JpegEncoder(quality: $quality))->save($pathFallback);
        } elseif ($ext === 'png') {
            $pathFallback = $dir . DIRECTORY_SEPARATOR . "{$base}-{$suffix}.png";
            $variant->encode(new PngEncoder())->save($pathFallback);
        } else {
            // format lain → jadikan jpg fallback
            $pathFallback = $dir . DIRECTORY_SEPARATOR . "{$base}-{$suffix}.jpg";
            $variant->encode(new JpegEncoder(quality: $quality))->save($pathFallback);
        }

        // Simpan WebP
        $pathWebp = $dir . DIRECTORY_SEPARATOR . "{$base}-{$suffix}.webp";
        $encoded = $variant->encode(new WebpEncoder(quality: $quality));
        file_put_contents($pathWebp, (string) $encoded);
    }
}
