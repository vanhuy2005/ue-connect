<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Preview private media.
     */
    public function preview(Request $request, ?Media $media = null): Response
    {
        if (! $media) {
            abort(404);
        }

        if ($media->visibility !== 'public' && ! $request->hasValidSignature()) {
            abort(403, 'Yêu cầu không hợp lệ hoặc đã hết hạn.');
        }

        Gate::authorize('view', $media);

        $asset = $this->resolveAsset($media, $request->query('variant'));

        if (! Storage::disk($asset['disk'])->exists($asset['path'])) {
            abort(404, 'File không tồn tại.');
        }

        return $this->serveFile($asset['disk'], $asset['path']);
    }

    /**
     * Download private media.
     */
    public function download(Request $request, Media $media): Response
    {
        Gate::authorize('view', $media);

        if (! Storage::disk($media->primary_disk)->exists($media->primary_path)) {
            abort(404, 'File không tồn tại.');
        }

        return Storage::disk($media->primary_disk)->download(
            $media->primary_path,
            $media->original_filename
        );
    }

    /**
     * Serve a file correctly depending on the storage driver (local vs cloud).
     */
    /**
     * @return array{disk: string, path: string}
     */
    protected function resolveAsset(Media $media, mixed $variantName): array
    {
        if (is_string($variantName) && $variantName !== '') {
            $variant = $media->variant($variantName);

            if ($variant instanceof MediaVariant) {
                return [
                    'disk' => $variant->disk,
                    'path' => $variant->path,
                ];
            }
        }

        return [
            'disk' => $media->primary_disk,
            'path' => $media->primary_path,
        ];
    }

    protected function serveFile(string $disk, string $path): Response
    {
        $driver = Storage::disk($disk)->getDriver();

        // Local storage can return direct BinaryFileResponse
        if ($disk === 'local' || $disk === 'private' || $disk === 'public') {
            return new BinaryFileResponse(Storage::disk($disk)->path($path));
        }

        // Cloud storage stream download
        $mimeType = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        return new StreamedResponse(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }
}
