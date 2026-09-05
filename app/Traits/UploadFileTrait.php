<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadFileTrait
{
    public function uploadImage(string $folder, UploadedFile $image): string
    {
        $extension = $image->getClientOriginalExtension();
        $fileName = time() . '-' . Str::random(10) . '.' . $extension;

        return $image->storeAs('images/' . trim($folder, '/'), $fileName, 'public');
    }

    public function uploadFile(string $folder, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '-' . Str::random(10) . '.' . $extension;

        return $file->storeAs('files/' . trim($folder, '/'), $fileName, 'public');
    }

    public function deletePublicFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
