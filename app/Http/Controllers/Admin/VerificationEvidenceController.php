<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationEvidence;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VerificationEvidenceController extends Controller
{
    /**
     * Stream a private verification evidence file securely for admin review.
     */
    public function show(VerificationEvidence $evidence): BinaryFileResponse
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

        $filePath = Storage::disk($mediaFile->disk)->path($mediaFile->path);

        return response()->file($filePath, [
            'Content-Type' => $mediaFile->mime_type,
            'Content-Disposition' => 'inline; filename="'.$mediaFile->original_name.'"',
        ]);
    }
}
