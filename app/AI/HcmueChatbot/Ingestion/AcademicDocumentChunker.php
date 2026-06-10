<?php

namespace App\AI\HcmueChatbot\Ingestion;

class AcademicDocumentChunker
{
    /**
     * Chunk document text and return an array of chunk datasets.
     *
     * @param  string  $text  Raw text extracted from document (pages separated by \f).
     * @return array<array{
     *   chunk_index: int,
     *   chunk_text: string,
     *   page_start: int,
     *   page_end: int,
     *   part: ?string,
     *   chapter: ?string,
     *   section: ?string,
     *   article: ?string,
     *   clause: ?string
     * }>
     */
    public function chunk(string $text): array
    {
        $pages = explode("\f", $text);

        // Remove empty pages at the end
        if (end($pages) === '') {
            array_pop($pages);
        }
        if (empty($pages)) {
            return [];
        }

        $chunks = [];
        $chunkIndex = 0;

        $currentPart = null;
        $currentChapter = null;
        $currentSection = null;
        $currentArticle = null;
        $currentChunkText = [];
        $chunkPageStart = 1;
        $hasArticles = false;

        // Regex patterns (case insensitive, supporting Vietnamese unicode)
        $partPattern = '/^\s*Phần\s+([A-Z0-9IVXLCDM]+|thứ\s+[a-zđăâêôơư]+)[\.:\s\-–—]*/ui';
        $chapterPattern = '/^\s*Chương\s+([A-Z0-9IVXLCDM]+)[\.:\s\-–—]*/ui';
        $sectionPattern = '/^\s*Mục\s+([0-9IVXLCDM]+)[\.:\s\-–—]*/ui';
        $articlePattern = '/^\s*Điều\s+([0-9]+)[\.:\s\-–—]*/ui';

        // First pass: scan for regulation structure (Điều/Article)
        foreach ($pages as $pageIdx => $pageText) {
            $pageNumber = $pageIdx + 1;
            $lines = explode("\n", $pageText);

            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (empty($trimmedLine)) {
                    continue;
                }

                // Check Part
                if (preg_match($partPattern, $trimmedLine)) {
                    $currentPart = $trimmedLine;

                    continue;
                }

                // Check Chapter
                if (preg_match($chapterPattern, $trimmedLine)) {
                    $currentChapter = $trimmedLine;

                    continue;
                }

                // Check Section
                if (preg_match($sectionPattern, $trimmedLine)) {
                    $currentSection = $trimmedLine;

                    continue;
                }

                // Check Article
                if (preg_match($articlePattern, $trimmedLine)) {
                    $hasArticles = true;
                    // Save previous article chunk if it exists
                    if ($currentChunkText) {
                        $chunks[] = [
                            'chunk_index' => $chunkIndex++,
                            'chunk_text' => trim(implode("\n", $currentChunkText)),
                            'page_start' => $chunkPageStart,
                            'page_end' => $pageNumber,
                            'part' => $currentPart,
                            'chapter' => $currentChapter,
                            'section' => $currentSection,
                            'article' => $currentArticle ?: 'Preamble',
                            'clause' => null,
                        ];
                    }

                    $currentArticle = $trimmedLine;
                    $currentChunkText = [$trimmedLine];
                    $chunkPageStart = $pageNumber;

                    continue;
                }

                // Collect text
                $currentChunkText[] = $line;
            }
        }

        // Save last chunk of the article flow
        if ($hasArticles && ! empty($currentChunkText)) {
            $chunks[] = [
                'chunk_index' => $chunkIndex++,
                'chunk_text' => trim(implode("\n", $currentChunkText)),
                'page_start' => $chunkPageStart,
                'page_end' => count($pages),
                'part' => $currentPart,
                'chapter' => $currentChapter,
                'section' => $currentSection,
                'article' => $currentArticle ?: 'Preamble',
                'clause' => null,
            ];
        }

        // If no articles were found, fallback to paragraph/page-based chunking
        if (! $hasArticles) {
            $chunks = $this->fallbackChunking($pages);
        }

        return $chunks;
    }

    /**
     * Fallback chunker for unstructured documents (e.g. general policies, markdown).
     * Splits text into blocks of ~800 to 1200 characters.
     */
    protected function fallbackChunking(array $pages): array
    {
        $chunks = [];
        $chunkIndex = 0;

        foreach ($pages as $pageIdx => $pageText) {
            $pageNumber = $pageIdx + 1;

            // Split page by paragraphs
            $paragraphs = explode("\n\n", $pageText);
            $currentChunk = '';

            foreach ($paragraphs as $para) {
                $para = trim($para);
                if (empty($para)) {
                    continue;
                }

                if (strlen($currentChunk) + strlen($para) > 1000) {
                    if (! empty($currentChunk)) {
                        $chunks[] = [
                            'chunk_index' => $chunkIndex++,
                            'chunk_text' => trim($currentChunk),
                            'page_start' => $pageNumber,
                            'page_end' => $pageNumber,
                            'part' => null,
                            'chapter' => null,
                            'section' => null,
                            'article' => null,
                            'clause' => null,
                        ];
                    }
                    $currentChunk = $para;
                } else {
                    $currentChunk = empty($currentChunk) ? $para : $currentChunk."\n\n".$para;
                }
            }

            if (! empty($currentChunk)) {
                $chunks[] = [
                    'chunk_index' => $chunkIndex++,
                    'chunk_text' => trim($currentChunk),
                    'page_start' => $pageNumber,
                    'page_end' => $pageNumber,
                    'part' => null,
                    'chapter' => null,
                    'section' => null,
                    'article' => null,
                    'clause' => null,
                ];
            }
        }

        return $chunks;
    }
}
