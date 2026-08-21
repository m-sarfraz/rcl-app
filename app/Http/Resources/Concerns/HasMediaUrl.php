<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMediaUrl
{
    /**
     * Absolute URL for an uploaded file. The mobile app cannot resolve relative
     * storage paths, so every image leaves the API fully qualified or null.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}
