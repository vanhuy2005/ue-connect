<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationEvidence;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class VerificationEvidenceController extends Controller
{
    /**
     * Stream a private verification evidence file securely for admin review.
     */
    public function show(VerificationEvidence $evidence): Response
    {
        // 1. Authorization: check if the authenticated user has the 'review_verification' permission
        if (! auth()->user() || ! auth()->user()->can('review_verification')) {
            abort(403, 'Bạn không có quyền xem tài liệu xác thực này.');
        }

        $mediaFile = $evidence->mediaFile;
        if (! $mediaFile) {
            abort(404, 'Không tìm thấy tệp đính kèm.');
        }

        // 2. Stream the file from the private storage disk
        if (! Storage::disk($mediaFile->disk)->exists($mediaFile->path)) {
            abort(404, 'Tệp tin không tồn tại trên hệ thống lưu trữ.');
        }

        // P0-5: Audit log — do not include raw file path to avoid info disclosure
        AuditLogService::log(
            actorId: auth()->id(),
            actorType: 'admin',
            actionKey: 'admin.evidence.preview',
            targetType: 'verification_evidence',
            targetId: $evidence->id,
            contextType: 'verification_request',
            contextId: $evidence->verification_request_id,
            metadata: [
                'original_name' => $mediaFile->original_name,
                'mime_type' => $mediaFile->mime_type,
                'size_bytes' => $mediaFile->size_bytes,
            ]
        );

        $disk = $mediaFile->disk;
        $path = $mediaFile->path;

        // Local storage can return direct BinaryFileResponse
        if ($disk === 'local' || $disk === 'private' || $disk === 'public') {
            $filePath = Storage::disk($disk)->path($path);

            return response()->file($filePath, [
                'Content-Type' => $mediaFile->mime_type,
                'Content-Disposition' => 'inline; filename="'.$mediaFile->original_name.'"',
            ]);
        }

        // Cloud storage stream download
        return response()->stream(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mediaFile->mime_type,
            'Content-Disposition' => 'inline; filename="'.$mediaFile->original_name.'"',
        ]);
    }
}
