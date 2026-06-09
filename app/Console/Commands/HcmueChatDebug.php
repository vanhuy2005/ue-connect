<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use App\AI\HcmueChatbot\Chat\QueryRouterService;
use App\AI\HcmueChatbot\Chat\StructuredQueryPlannerService;
use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\Models\TrainingProgram;
use App\Models\SourceDocument;
use Illuminate\Console\Command;

class HcmueChatDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:chat:debug {question : The user academic question to debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trace and debug HCMUE chatbot retrieval routing, structured query mapping, RAG results, and composed answers';

    /**
     * Execute the console command.
     */
    public function handle(
        QuestionNormalizerService $normalizer,
        QueryRouterService $router,
        StructuredQueryPlannerService $planner,
        StructuredRetrievalService $structuredRetrieval,
        RagRetrievalService $ragRetrieval,
        AnswerComposerService $composer
    ): int {
        $question = $this->argument('question');

        $this->info("=================== HCMUE CHAT PIPELINE TRACE ===================");
        $this->line("Original Question:   \"{$question}\"");

        // Step 1: Normalize
        $norm = $normalizer->normalize($question);
        $normalizedQuestion = $norm['normalized_question'];
        $detected = $norm['detected_terms'];

        $this->line("Normalized Question: \"{$normalizedQuestion}\"");
        $this->newLine();

        $this->info("Detected Entities:");
        $this->line(" - cohort:           " . ($detected['cohort'] ?: 'N/A'));
        $this->line(" - major:            " . ($detected['major'] ?: 'N/A'));
        $this->line(" - faculty:          " . ($detected['faculty'] ?: 'N/A'));
        $this->line(" - course:           " . ($detected['course'] ?: 'N/A'));
        $this->line(" - policy_topic:     " . ($detected['policy_topic'] ?: 'N/A'));
        $this->newLine();

        // Step 2: Route
        $route = $router->route($normalizedQuestion, $detected);
        $this->info("Router Decision:");
        $this->line(" - expected route:   (determined by query properties)");
        $this->line(" - actual route:     " . $route['source']);
        $this->line(" - intent matched:   " . $route['intent']);
        $this->line(" - confidence score: " . number_format($route['confidence'], 2));
        $this->line(" - reason:           " . $route['reason']);
        $this->newLine();

        $structuredDbResult = null;
        $ragChunks = [];

        // Step 3: Structured Query Planner & Retrieval
        if (in_array($route['source'], ['structured_db', 'hybrid'])) {
            $this->info("Structured Query Plan:");
            $plan = $planner->plan($route, $normalizedQuestion);
            $this->line(" - query_type:       " . $plan['query_type']);
            $this->line(" - filters:          " . json_encode($plan['filters'], JSON_UNESCAPED_UNICODE));

            if ($plan['requires_clarification']) {
                $this->warn("  -> Clarification Required: " . $plan['clarification_question']);
                return self::FAILURE;
            }

            $structuredDbResult = $structuredRetrieval->retrieve($plan);

            $this->newLine();
            $this->info("Structured Database Retrieval Result:");
            if ($structuredDbResult['success']) {
                $this->line(" - status:           SUCCESS");
                $this->line(" - metadata:         " . json_encode($structuredDbResult['metadata'], JSON_UNESCAPED_UNICODE));
                
                $data = $structuredDbResult['data'];
                if (is_array($data) && isset($data['total_credits'])) {
                    $this->line(" - total_credits:    " . $data['total_credits']);
                } elseif (method_exists($data, 'toArray')) {
                    $this->line(" - records count:    " . count($data));
                } else {
                    $this->line(" - data payload:     " . json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            } else {
                $this->error(" - status:           FAILED");
                $this->error(" - message:          " . $structuredDbResult['message']);
                $this->printStructuredSuggestions($detected);
                return self::FAILURE;
            }
            $this->newLine();
        }

        // Step 4: RAG Retrieval
        if (in_array($route['source'], ['rag', 'hybrid'])) {
            $this->info("RAG Retrieval (Qdrant):");
            $ragFilters = [];
            if ($detected['cohort']) {
                $ragFilters['cohort'] = $detected['cohort'];
            }
            
            $ragChunks = $ragRetrieval->retrieve($normalizedQuestion, $ragFilters);
            $this->line(" - points retrieved: " . count($ragChunks));
            foreach ($ragChunks as $index => $chunk) {
                $score = number_format($chunk['score'] ?? 0.0, 2);
                $rerank = isset($chunk['rerank_score']) ? number_format($chunk['rerank_score'], 2) : 'N/A';
                $this->line("   [" . ($index + 1) . "] Document: {$chunk['document_name']} | Score: {$score} | Rerank: {$rerank}");
                $this->line("       Text: " . mb_substr(trim($chunk['chunk_text']), 0, 100) . "...");
            }
            $this->newLine();
        }

        // Step 5: Compose Answer
        $this->info("Answer Composition:");
        $composed = $composer->compose(
            $question,
            $normalizedQuestion,
            $route,
            $structuredDbResult,
            $ragChunks
        );

        $this->line(" - provider used:    " . $composed['model_provider'] . " (" . $composed['model_name'] . ")");
        $this->line(" - latency:          " . $composed['latency_ms'] . " ms");
        $this->newLine();

        $this->info("Final Answer Output:");
        $this->comment($composed['answer_text']);
        $this->newLine();

        $this->info("=================================================================");
        return self::SUCCESS;
    }

    /**
     * Print useful fallback tips when structured retrieval fails.
     */
    protected function printStructuredSuggestions(array $detected): void
    {
        $this->newLine();
        $this->info("=== DB DIAGNOSTIC SUGGESTIONS ===");

        $cohort = $detected['cohort'];
        $major = $detected['major'];

        if ($cohort) {
            $programsCount = TrainingProgram::whereHas('cohort', function ($q) use ($cohort) {
                $q->where('cohort_name', 'like', "%{$cohort}%");
            })->count();
            $this->line("- Training programs with cohort '{$cohort}': {$programsCount} records.");
        }

        if ($major) {
            $matchingMajors = \App\Models\Major::where('name', 'like', "%{$major}%")
                ->orWhere('normalized_name', 'like', "%{$major}%")
                ->pluck('name')
                ->toArray();
            if (empty($matchingMajors)) {
                $this->warn("- No matching Majors found in database for term: '{$major}'.");
            } else {
                $this->line("- Matching Majors in DB: " . implode(', ', $matchingMajors));
            }
        }

        if ($cohort && $major) {
            $this->line("- Checking general cohort/major training documents in Source Documents...");
            $docs = SourceDocument::where('document_type', 'training_program')
                ->where('cohort', 'like', "%{$cohort}%")
                ->get();
            $this->line("  Found " . $docs->count() . " source documents matching document_type=training_program and cohort='{$cohort}':");
            foreach ($docs as $d) {
                $this->line("   * ID: {$d->id} | Title: {$d->title} | Path: {$d->file_path}");
            }
        }
    }
}
