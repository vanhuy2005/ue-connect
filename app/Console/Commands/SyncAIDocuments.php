<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\LLM\GeminiKeyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SyncAIDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:sync-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync PDF documents to Gemini File API for Chatbot knowledge base.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $primaryKey = config('ai-verification.providers.gemini_flash.api_key');
        $keyManager = new GeminiKeyManager($primaryKey ? [$primaryKey] : null);

        if (empty($keyManager->getKeys())) {
            $this->error('Gemini API Key is not configured in .env (GEMINI_API_KEY).');

            return Command::FAILURE;
        }

        $directories = [
            base_path('database/AI/Chuongtrinhdaotao'),
            base_path('database/AI/Sotaysinhvien'),
        ];

        $pdfFiles = [];

        $this->info('Scanning for PDF documents...');

        foreach ($directories as $dir) {
            if (File::exists($dir)) {
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    if (strtolower($file->getExtension()) === 'pdf') {
                        $pdfFiles[] = $file;
                    }
                }
            } else {
                $this->warn("Directory not found: $dir");
            }
        }

        if (empty($pdfFiles)) {
            $this->info('No PDF files found to upload.');

            return Command::SUCCESS;
        }

        // Lọc lấy 15 file mới nhất để tránh vượt quá giới hạn 1 Triệu Token của Gemini và lỗi Memory Leak
        usort($pdfFiles, function ($a, $b) {
            return $b->getMTime() <=> $a->getMTime();
        });

        $pdfFiles = array_slice($pdfFiles, 0, 15);

        $this->info('Found many files. Selecting '.count($pdfFiles).' most recent files to prevent token overflow...');

        $uploadedData = [];
        $bar = $this->output->createProgressBar(count($pdfFiles));
        $bar->start();

        foreach ($pdfFiles as $file) {
            $content = File::get($file->getRealPath());

            try {
                $response = $keyManager->run(function (string $apiKey) use ($content) {
                    $url = "https://generativelanguage.googleapis.com/upload/v1beta/files?uploadType=media&key={$apiKey}";

                    return Http::withoutVerifying()
                        ->timeout(60)
                        ->withBody($content, 'application/pdf')
                        ->post($url)
                        ->throw();
                });

                $data = $response->json();
                if (isset($data['file']['uri'])) {
                    $uploadedData[] = [
                        'name' => $file->getFilename(),
                        'mime_type' => $data['file']['mimeType'] ?? 'application/pdf',
                        'file_uri' => $data['file']['uri'],
                        'gemini_name' => $data['file']['name'],
                    ];
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error('Failed/Exception uploading '.$file->getFilename().': '.$e->getMessage());
            }

            // Giải phóng bộ nhớ và tránh Rate Limit của Google (15 requests / minute)
            unset($content);
            sleep(4);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (! empty($uploadedData)) {
            // Gemini files expire in 48 hours. Cache for 47 hours.
            Cache::put('ai_chatbot_documents', $uploadedData, now()->addHours(47));
            $this->info('Successfully uploaded and cached '.count($uploadedData).' documents.');
        } else {
            $this->warn('No documents were successfully uploaded.');
        }

        return Command::SUCCESS;
    }
}
