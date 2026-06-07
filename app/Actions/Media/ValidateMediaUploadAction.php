<?php

namespace App\Actions\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ValidateMediaUploadAction
{
    /**
     * Validate an uploaded media file against collection limits and security rules.
     *
     * @throws ValidationException
     */
    public function execute(UploadedFile $file, string $collection): void
    {
        $allowedCollections = [
            'avatar',
            'profile_cover',
            'community_avatar',
            'community_cover',
            'post_image',
            'comment_image',
            'message_attachment',
            'verification_evidence',
            'report_evidence',
        ];

        if (! in_array($collection, $allowedCollections, true)) {
            $this->throwError('collection', 'Loại media không được hỗ trợ.');
        }

        // 1. Extension validation (reject SVGs, GIFs, videos, HEIC)
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if ($collection === 'verification_evidence') {
            $allowedExtensions = array_merge($allowedExtensions, ['pdf', 'docx', 'zip']);
        }

        if (! in_array($extension, $allowedExtensions, true)) {
            $msg = $collection === 'verification_evidence'
                ? 'Định dạng file không được hỗ trợ. Chỉ cho phép các file jpg, jpeg, png, webp, pdf, docx, zip.'
                : 'Định dạng file không được hỗ trợ. Chỉ cho phép các file jpg, jpeg, png, webp.';
            $this->throwError('file', $msg);
        }

        // 2. Strict content sniffing (real MIME/content verification)
        $realMime = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if ($collection === 'verification_evidence') {
            $allowedMimes = array_merge($allowedMimes, [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/x-zip-compressed',
            ]);
        }

        if (! in_array($realMime, $allowedMimes, true)) {
            $this->throwError('file', 'File tải lên không hợp lệ hoặc bị giả mạo định dạng.');
        }

        // 3. Reject SVGs, GIFs, and other formats explicitly
        if (str_contains($realMime, 'svg') || str_contains($realMime, 'gif')) {
            $this->throwError('file', 'Các định dạng ảnh động SVG/GIF không được chấp nhận vì lý do an toàn.');
        }

        // 4. File size validation by collection
        $sizeBytes = $file->getSize();
        $maxMb = match ($collection) {
            'avatar', 'community_avatar' => config('media.limits.avatar_mb', 5),
            'profile_cover', 'community_cover' => config('media.limits.cover_mb', 8),
            'post_image' => config('media.limits.post_image_mb', 10),
            'message_attachment' => config('media.limits.message_image_mb', 10),
            'verification_evidence' => config('media.limits.verification_evidence_mb', 10),
            default => 10,
        };

        $maxBytes = $maxMb * 1024 * 1024;
        if ($sizeBytes > $maxBytes) {
            $this->throwError('file', "Kích thước file vượt quá giới hạn cho phép ({$maxMb} MB).");
        }

        // 5. Image dimensions validation (ensure it is a readable image)
        if (str_starts_with($realMime, 'image/')) {
            $dimensions = @getimagesizefromstring($file->get());
            if (! $dimensions) {
                $this->throwError('file', 'Không thể đọc thông tin kích thước của ảnh.');
            }
        }
    }

    /**
     * Helper to throw clean validation exceptions.
     */
    protected function throwError(string $key, string $message): void
    {
        throw ValidationException::withMessages([
            $key => [$message],
        ]);
    }
}
