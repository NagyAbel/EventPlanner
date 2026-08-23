<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadEventCovers extends Command
{
    protected $signature = 'events:download-covers
                            {count=100 : Number of images to download}
                            {--force : Download even if the directory already contains images}';

    protected $description = 'Download random WebP event cover images';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        if ($count < 1) {
            $this->error('Count must be at least 1.');

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $directory = 'test_event_covers';

        $disk->makeDirectory($directory);

        if (! $this->option('force')) {
            $existing = count($disk->files($directory));

            if ($existing >= $count) {
                $this->info("Already have {$existing} event covers.");

                return self::SUCCESS;
            }

            $this->info("Found {$existing} existing images. Downloading " . ($count - $existing) . " more.");
            $count -= $existing;
        }

        $progress = $this->output->createProgressBar($count);
        $progress->start();

        $downloaded = 0;
        $attempts = 0;
        $maxAttempts = $count * 3;

        while ($downloaded < $count && $attempts < $maxAttempts) {
            $attempts++;

            try {
                /*
                 * Picsum returns an actual image.
                 *
                 * The random query prevents repeatedly receiving
                 * the same image.
                 */
                $url = 'https://picsum.photos/seed/' . Str::uuid() . '/1200/630';

                $response = Http::timeout(30)
                    ->retry(2, 100)
                    ->get($url);

                if (! $response->successful()) {
                    continue;
                }

                $contents = $response->body();

                if ($contents === '') {
                    continue;
                }

                $filename = Str::uuid() . '.webp';

                /*
                 * Picsum normally returns JPEG, so we need to convert
                 * it to WebP before storing it.
                 */
                $source = @imagecreatefromstring($contents);

                if ($source === false) {
                    continue;
                }

                $tempPath = tempnam(sys_get_temp_dir(), 'event-cover-');

                imagewebp($source, $tempPath, 85);

                imagedestroy($source);

                $disk->put(
                    "{$directory}/{$filename}",
                    file_get_contents($tempPath)
                );

                unlink($tempPath);

                $downloaded++;
                $progress->advance();
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Download failed: {$e->getMessage()}");
            }
        }

        $progress->finish();
        $this->newLine(2);

        $total = count($disk->files($directory));

        if ($downloaded < $count) {
            $this->warn("Only downloaded {$downloaded} images.");
            $this->info("Total images available: {$total}");

            return self::FAILURE;
        }

        $this->info("Successfully downloaded {$downloaded} event covers.");
        $this->info("Total covers available: {$total}");

        return self::SUCCESS;
    }
}