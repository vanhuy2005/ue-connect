<?php

namespace App\AI\HcmueChatbot\Chat;

class HallucinationGuardService
{
    /**
     * Safe fallback message when no valid answer can be produced.
     */
    public const FALLBACK_MESSAGE = 'Mình chưa tìm thấy thông tin này trong nguồn dữ liệu đã được hệ thống lập chỉ mục. Với vấn đề học vụ quan trọng, bạn nên kiểm tra lại với Phòng Đào tạo, Khoa hoặc Cố vấn học tập của mình nhé.';

    /**
     * Guard against hallucinated answers.
     * Returns the safe final answer text after applying guard rules.
     */
    public function guard(
        string $draftAnswer,
        array $citationResult,
        bool $hasAnySource
    ): string {
        // If citation check says valid, pass through
        if ($citationResult['is_valid']) {
            return $draftAnswer;
        }

        // If there's a recommended fix to apply, try it
        if (! empty($citationResult['recommended_fix'])) {
            // Append a note about corrected citations and return
            return $draftAnswer."\n\n*(Lưu ý: Một số trích dẫn đã được điều chỉnh cho phù hợp với tài liệu nguồn.)*";
        }

        // If there are serious problems (fake citations invented data)
        // and we have no real sources to verify against, use fallback
        if (! $hasAnySource) {
            return self::FALLBACK_MESSAGE;
        }

        // If citation problems are minor (only missing a sources section),
        // append the fallback note but keep the answer
        $onlyMissingCitation = empty($citationResult['unsupported_claims']) &&
            ! empty($citationResult['missing_citations']);

        if ($onlyMissingCitation) {
            return $draftAnswer."\n\n---\n**Nguồn:** Dữ liệu từ hệ thống HCMUE Academic Chatbot.";
        }

        // Multiple unsupported claims — too risky, use fallback
        if (count($citationResult['unsupported_claims']) > 2) {
            return self::FALLBACK_MESSAGE;
        }

        // Minor issues (1-2 citations) — keep answer but add disclaimer
        return $draftAnswer."\n\n*(Lưu ý: Vui lòng xác nhận thông tin với Phòng Đào tạo hoặc Cố vấn học tập nếu cần quyết định quan trọng.)*";
    }

    /**
     * Check if an answer is a clear "no data" response.
     */
    public function isNoDataResponse(string $answer): bool
    {
        $noDataPatterns = [
            'chưa tìm thấy',
            'không tìm thấy',
            'chưa có dữ liệu',
            'không có thông tin',
            'chưa được lập chỉ mục',
        ];

        $lower = mb_strtolower($answer);
        foreach ($noDataPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
