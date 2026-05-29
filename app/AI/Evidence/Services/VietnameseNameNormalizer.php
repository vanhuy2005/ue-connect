<?php

namespace App\AI\Evidence\Services;

class VietnameseNameNormalizer
{
    /**
     * Normalize a Vietnamese name for comparison.
     * Strips diacritics, lowercases, trims whitespace.
     */
    public function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $name = $this->stripDiacritics($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return trim($name);
    }

    /**
     * Check if two names match after normalization.
     * Returns similarity score 0.0–1.0.
     */
    public function similarity(string $nameA, string $nameB): float
    {
        $a = $this->normalize($nameA);
        $b = $this->normalize($nameB);

        if ($a === $b) {
            return 1.0;
        }

        // Partial match: one contains the other
        if (str_contains($a, $b) || str_contains($b, $a)) {
            return 0.8;
        }

        // Token overlap
        $tokensA = explode(' ', $a);
        $tokensB = explode(' ', $b);
        $intersection = array_intersect($tokensA, $tokensB);
        $union = array_unique(array_merge($tokensA, $tokensB));

        if (empty($union)) {
            return 0.0;
        }

        return count($intersection) / count($union);
    }

    private function stripDiacritics(string $text): string
    {
        $patterns = [
            '/[àáạảãâầấậẩẫăằắặẳẵ]/u' => 'a',
            '/[èéẹẻẽêềếệểễ]/u' => 'e',
            '/[ìíịỉĩ]/u' => 'i',
            '/[òóọỏõôồốộổỗơờớợởỡ]/u' => 'o',
            '/[ùúụủũưừứựửữ]/u' => 'u',
            '/[ỳýỵỷỹ]/u' => 'y',
            '/[đ]/u' => 'd',
            '/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/u' => 'a',
            '/[ÈÉẸẺẼÊỀẾỆỂỄ]/u' => 'e',
            '/[ÌÍỊỈĨ]/u' => 'i',
            '/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/u' => 'o',
            '/[ÙÚỤỦŨƯỪỨỰỬỮ]/u' => 'u',
            '/[ỲÝỴỶỸ]/u' => 'y',
            '/[Đ]/u' => 'd',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $text) ?? $text;
    }
}
