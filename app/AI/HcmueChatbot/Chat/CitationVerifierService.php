<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\Prompts\CitationVerifierPrompt;
use Illuminate\Support\Facades\Log;

class CitationVerifierService
{
    /**
     * Verify that all citations in the draft answer are supported by sources.
     *
     * @return array{
     *   is_valid: bool,
     *   problems: array,
     *   unsupported_claims: array,
     *   missing_citations: array,
     *   recommended_fix: ?string
     * }
     */
    public function verify(string $draftAnswer, array $ragChunks, ?array $structuredDbResult): array
    {
        // If no answer, nothing to verify
        if (empty(trim($draftAnswer))) {
            return $this->validResult();
        }

        // Build context string from available sources
        $contextUsed = $this->buildContextString($ragChunks, $structuredDbResult);

        // Fast path: if answer has no citation markers at all, it may be missing citations
        $hasCitationMarkers = preg_match('/\[.+?\]|Nguồn:|Tài liệu:|Theo quy/u', $draftAnswer);

        if (! $hasCitationMarkers && ! empty($ragChunks)) {
            return [
                'is_valid' => false,
                'problems' => ['Câu trả lời thiếu phần Nguồn.'],
                'unsupported_claims' => [],
                'missing_citations' => ['Cần bổ sung phần "Nguồn" cuối câu trả lời.'],
                'recommended_fix' => null,
            ];
        }

        // If both sources are empty, no need to verify against them
        if (empty($ragChunks) && empty($structuredDbResult)) {
            return $this->validResult();
        }

        // LLM-based citation verification
        return $this->llmVerify($draftAnswer, $contextUsed);
    }

    /**
     * Use LLM to verify citations in the draft.
     */
    private function llmVerify(string $draft, string $context): array
    {
        try {
            $prompt = CitationVerifierPrompt::render([
                'answer_draft' => $draft,
                'context_used' => $context,
            ]);

            $llm = LlmGateway::driver();
            $response = $llm->generate($prompt, ['json_mode' => true, 'temperature' => 0.1]);
            $text = trim($response['text']);

            // Strip markdown fences
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);

            $parsed = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
                Log::warning('CitationVerifierService: LLM returned invalid JSON.', ['raw' => $text]);

                return $this->validResult(); // Permissive fallback
            }

            $allValid = (bool) ($parsed['all_citations_valid'] ?? true);
            $invalidCitations = $parsed['invalid_citations'] ?? [];

            $problems = array_map(fn ($c) => "Citation giả: {$c['citation']} — {$c['reason']}", $invalidCitations);

            $recommendedFix = null;
            if (! empty($parsed['corrected_citations'])) {
                $fixes = array_filter(
                    array_map(fn ($c) => $c['new'] ?? null, $parsed['corrected_citations'])
                );
                if (! empty($fixes)) {
                    $recommendedFix = implode('; ', $fixes);
                }
            }

            return [
                'is_valid' => $allValid && empty($problems),
                'problems' => $problems,
                'unsupported_claims' => array_column($invalidCitations, 'citation'),
                'missing_citations' => [],
                'recommended_fix' => $recommendedFix,
            ];
        } catch (\Exception $e) {
            Log::error('CitationVerifierService LLM failed: '.$e->getMessage());

            return $this->validResult(); // Permissive on error — don't block answers
        }
    }

    /**
     * Build a context string from RAG chunks + structured DB.
     */
    private function buildContextString(array $ragChunks, ?array $structuredDbResult): string
    {
        $parts = [];

        if (! empty($ragChunks)) {
            foreach ($ragChunks as $chunk) {
                $parts[] = ($chunk['document_name'] ?? 'Tài liệu').': '.($chunk['chunk_text'] ?? '');
            }
        }

        if (! empty($structuredDbResult['data'])) {
            $parts[] = '[Dữ liệu CTĐT từ database]';
        }

        return implode("\n---\n", $parts);
    }

    /**
     * Return a passing verification result.
     */
    private function validResult(): array
    {
        return [
            'is_valid' => true,
            'problems' => [],
            'unsupported_claims' => [],
            'missing_citations' => [],
            'recommended_fix' => null,
        ];
    }
}
