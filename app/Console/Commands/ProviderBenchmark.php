<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\LLM\LlmGateway;
use Illuminate\Console\Command;

class ProviderBenchmark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:provider:benchmark {--limit=5 : Số câu hỏi chạy thử nghiệm}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'So sánh hiệu năng và tốc độ của các LLM Provider (Ollama vs Gemini vs OpenRouter)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('==================================================');
        $this->line('=== HCMUE CHATBOT LLM PROVIDER BENCHMARK ===');
        $this->line('==================================================');

        $limit = (int) $this->option('limit');
        $queries = array_slice([
            'Môn học Công nghệ thông tin K50 có bao nhiêu tín chỉ?',
            'Quy chế học vụ về cảnh báo học tập được quy định như thế nào?',
            'Chuẩn đầu ra của ngành Sư phạm Tin học bao gồm những gì?',
            'Điều kiện để sinh viên được xét tốt nghiệp ra trường là gì?',
            'Làm sao để đăng ký học cải thiện điểm học phần?',
        ], 0, $limit);

        // Providers to test
        $providers = [
            'gemini' => [
                'name' => 'gemini',
                'model' => config('ai.gemini.model', 'gemini-2.0-flash'),
            ],
            'ollama' => [
                'name' => 'ollama',
                'model' => config('ai.ollama.model', 'gemma2:2b'),
            ],
            'openrouter' => [
                'name' => 'openrouter',
                'model' => config('ai.openrouter.model', 'google/gemma-2-9b-it'),
            ],
        ];

        $results = [];

        foreach ($providers as $driverKey => $info) {
            $this->info("\nĐang kiểm tra Provider: ".strtoupper($info['name']).' (Model: '.$info['model'].')...');

            try {
                // Verify provider connectivity
                $provider = LlmGateway::driver($info['name'], $info['model']);

                $totalLatency = 0;
                $totalTokens = 0;
                $successCount = 0;

                foreach ($queries as $q) {
                    $start = microtime(true);
                    $response = $provider->generate('Hãy trả lời ngắn gọn trong 1 câu: '.$q);
                    $latency = (microtime(true) - $start) * 1000;

                    $tokens = $response['usage']['output_tokens'] ?? 0;
                    if ($tokens === 0 && ! empty($response['text'])) {
                        // Estimate
                        $tokens = mb_strlen($response['text']) / 4;
                    }

                    $totalLatency += $latency;
                    $totalTokens += $tokens;
                    $successCount++;
                }

                if ($successCount > 0) {
                    $avgLatency = $totalLatency / $successCount;
                    $speed = ($totalTokens / ($totalLatency / 1000)); // tokens/sec

                    $results[] = [
                        strtoupper($info['name']),
                        $info['model'],
                        number_format($avgLatency, 0).' ms',
                        number_format($speed, 2).' tok/s',
                        '<fg=green>Online</>',
                    ];
                }
            } catch (\Exception $e) {
                $this->error('Lỗi khi kết nối tới '.strtoupper($info['name']).': '.$e->getMessage());
                $results[] = [
                    strtoupper($info['name']),
                    $info['model'],
                    'N/A',
                    'N/A',
                    '<fg=red>Offline / Error</>',
                ];
            }
        }

        $this->info("\nBẢNG SO SÁNH HIỆU NĂNG LLM PROVIDERS:");
        $this->table(['Provider', 'Model', 'Độ trễ trung bình', 'Tốc độ tạo', 'Trạng thái'], $results);

        return self::SUCCESS;
    }
}
