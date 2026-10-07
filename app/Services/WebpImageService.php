<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WebpImageService
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
    ];

    public function store(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $quality = 82,
        ?string $filename = null
    ): string {
        if (! $file->isValid()) {
            throw new RuntimeException('Uploaded image is not valid.');
        }

        return $this->storeFromPath(
            $file->getRealPath(),
            (string) $file->getMimeType(),
            $directory,
            $disk,
            $quality,
            $filename
        );
    }

    public function storeContents(
        string $contents,
        ?string $mime,
        string $directory,
        string $disk = 'public',
        int $quality = 82,
        ?string $filename = null
    ): string {
        if ($contents === '') {
            throw new RuntimeException('Image contents are empty.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'egypttour-img-');

        if ($tmp === false) {
            throw new RuntimeException(
                'Unable to create temporary image file.'
            );
        }

        try {
            if (file_put_contents($tmp, $contents) === false) {
                throw new RuntimeException(
                    'Unable to write temporary image file.'
                );
            }

            /*
             * Never trust only the HTTP Content-Type.
             * Detect the real MIME from the downloaded bytes.
             */
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($tmp) ?: '';

            $resolvedMime = $detectedMime ?: (string) $mime;

            return $this->storeFromPath(
                $tmp,
                $resolvedMime,
                $directory,
                $disk,
                $quality,
                $filename
            );
        } finally {
            @unlink($tmp);
        }
    }

    private function storeFromPath(
        string $sourcePath,
        string $mime,
        string $directory,
        string $disk,
        int $quality,
        ?string $filename
    ): string {
        if (! is_file($sourcePath) || filesize($sourcePath) === 0) {
            throw new RuntimeException(
                'Source image does not exist or is empty.'
            );
        }

        $mime = $this->normalizeMime($mime);

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException(
                "Unsupported image type: {$mime}"
            );
        }

        $quality = max(1, min(100, $quality));

        $directory = trim($directory, '/');

        if ($filename) {
            $filename = pathinfo($filename, PATHINFO_FILENAME);

            $filename = preg_replace(
                '/[^A-Za-z0-9_-]+/',
                '-',
                $filename
            );

            $filename = trim((string) $filename, '-');
        }

        if (! $filename) {
            $filename = (string) Str::uuid();
        }

        $relativePath = $directory !== ''
            ? $directory . '/' . $filename . '.webp'
            : $filename . '.webp';

        Storage::disk($disk)->makeDirectory($directory);

        /*
         * If already a real WebP, don't re-encode unnecessarily.
         */
        if ($mime === 'image/webp') {
            $contents = file_get_contents($sourcePath);

            if ($contents === false) {
                throw new RuntimeException(
                    'Could not read WebP image.'
                );
            }

            if (! Storage::disk($disk)->put(
                $relativePath,
                $contents
            )) {
                throw new RuntimeException(
                    'Could not save WebP image.'
                );
            }

            return $relativePath;
        }

        $tmpBase = tempnam(
            sys_get_temp_dir(),
            'egypttour-webp-'
        );

        if ($tmpBase === false) {
            throw new RuntimeException(
                'Unable to create WebP temporary file.'
            );
        }

        @unlink($tmpBase);

        $tmpWebp = $tmpBase . '.webp';

        try {
            $this->convertToWebp(
                $sourcePath,
                $tmpWebp,
                $mime,
                $quality
            );

            if (
                ! is_file($tmpWebp) ||
                filesize($tmpWebp) === 0
            ) {
                throw new RuntimeException(
                    'WebP conversion created an empty file.'
                );
            }

            $contents = file_get_contents($tmpWebp);

            if ($contents === false) {
                throw new RuntimeException(
                    'Unable to read converted WebP.'
                );
            }

            if (! Storage::disk($disk)->put(
                $relativePath,
                $contents
            )) {
                throw new RuntimeException(
                    'Unable to save converted WebP.'
                );
            }

            return $relativePath;
        } finally {
            @unlink($tmpWebp);
        }
    }

    private function convertToWebp(
        string $source,
        string $destination,
        string $mime,
        int $quality
    ): void {
        $output = [];
        $exitCode = 1;

        /*
         * cwebp gives excellent results for ordinary JPEG/PNG.
         */
        if (
            in_array($mime, ['image/jpeg', 'image/png'], true) &&
            is_executable('/usr/bin/cwebp')
        ) {
            $command = sprintf(
                '%s -quiet -q %d -m 6 -alpha_q 100 %s -o %s 2>&1',
                escapeshellarg('/usr/bin/cwebp'),
                $quality,
                escapeshellarg($source),
                escapeshellarg($destination)
            );

            exec($command, $output, $exitCode);
        }

        /*
         * Preserve animated GIF where gif2webp is available.
         */
        elseif (
            $mime === 'image/gif' &&
            is_executable('/usr/bin/gif2webp')
        ) {
            $command = sprintf(
                '%s -quiet -q %d %s -o %s 2>&1',
                escapeshellarg('/usr/bin/gif2webp'),
                $quality,
                escapeshellarg($source),
                escapeshellarg($destination)
            );

            exec($command, $output, $exitCode);
        }

        /*
         * AVIF/GIF fallback via ImageMagick.
         */
        elseif (is_executable('/usr/bin/convert')) {
            $command = sprintf(
                '%s %s -auto-orient -strip -quality %d %s 2>&1',
                escapeshellarg('/usr/bin/convert'),
                escapeshellarg($source),
                $quality,
                escapeshellarg($destination)
            );

            exec($command, $output, $exitCode);
        }

        if (
            $exitCode !== 0 ||
            ! is_file($destination) ||
            filesize($destination) === 0
        ) {
            @unlink($destination);

            throw new RuntimeException(
                'WebP conversion failed: ' .
                implode("\n", $output)
            );
        }
    }

    private function normalizeMime(string $mime): string
    {
        $mime = strtolower(
            trim(explode(';', $mime)[0])
        );

        return match ($mime) {
            'image/jpg', 'image/pjpeg' => 'image/jpeg',
            'image/x-png' => 'image/png',
            default => $mime,
        };
    }
}
