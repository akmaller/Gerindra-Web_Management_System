<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Support\ImageCompressor;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--disk=public} {--path=uploads} {--max=1600} {--quality=82}';
    protected $description = 'Optimize & make WebP for existing images';

    public function handle(): int
    {
        $disk = $this->option('disk');
        $base = $this->option('path');
        $max = (int) $this->option('max');
        $q = (int) $this->option('quality');

        $files = collect(Storage::disk($disk)->allFiles($base))
            ->filter(fn($f) => preg_match('/\.(jpe?g|png)$/i', $f));

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        foreach ($files as $path) {
            try {
                ImageCompressor::run($path, $disk, $max, $q, true);
            } catch (\Throwable $e) {
                $this->warn("Fail: $path -> " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done.');
        return self::SUCCESS;
    }
}
