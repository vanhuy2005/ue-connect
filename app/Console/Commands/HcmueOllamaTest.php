<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\LLM\OllamaProvider;
use Illuminate\Console\Command;

class HcmueOllamaTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:ollama:test
                            {--model= : Model name to test (default from OLLAMA_CHAT_MODEL)}
                            {--prompt=Xin chào, bạn là ai? : Prompt to send as test message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Ollama local LLM server connectivity, model availability, and chat response';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->option('model') ?: config('ai.ollama.chat_model', 'gemma4:e2b');
        $prompt = $this->option('prompt');
        $baseUrl = config('ai.ollama.base_url', 'http://127.0.0.1:11434');

        $this->newLine();
        $this->line('  <fg=cyan;options=bold>HCMUE Chatbot — Ollama Local LLM Test</>');
        $this->line('  ─────────────────────────────────────');
        $this->line("  Base URL : <fg=yellow>{$baseUrl}</>");
        $this->line("  Model    : <fg=yellow>{$model}</>");
        $this->line("  Prompt   : <fg=yellow>{$prompt}</>");
        $this->newLine();

        $provider = new OllamaProvider($baseUrl, $model);

        // 1. Check server reachability
        $this->line('  <fg=white>[1] Checking Ollama server...</>');
        if (! $provider->isServerReachable()) {
            $this->line("  <fg=red>[FAIL]</> Ollama server not reachable at <fg=yellow>{$baseUrl}</>");
            $this->newLine();
            $this->line('  → Make sure Ollama is running: <fg=cyan>ollama serve</>');

            return self::FAILURE;
        }
        $this->line('  <fg=green>[OK]</> Ollama server is reachable');

        // 2. Check model installation
        $this->line("  <fg=white>[2] Checking model '{$model}'...</>");
        $installedModels = $provider->getInstalledModels();

        $isInstalled = collect($installedModels)->contains(
            fn ($name) => str_starts_with($name, $model)
        );

        if (! $isInstalled) {
            $this->line("  <fg=red>[FAIL]</> Model <fg=yellow>{$model}</> not found.");
            $this->newLine();
            $this->line("  → Install it: <fg=cyan>ollama pull {$model}</>");

            if (! empty($installedModels)) {
                $this->newLine();
                $this->line('  Installed models:');
                foreach ($installedModels as $name) {
                    $this->line("    · <fg=yellow>{$name}</>");
                }
            }

            return self::FAILURE;
        }
        $this->line("  <fg=green>[OK]</> Model <fg=yellow>{$model}</> is installed");

        // 3. Send test chat
        $this->line('  <fg=white>[3] Sending test chat request...</>');
        $startTime = microtime(true);

        try {
            $response = $provider->generate($prompt, ['temperature' => 0.3]);
            $latency = round((microtime(true) - $startTime) * 1000);

            $this->line("  <fg=green>[OK]</> Response received in <fg=yellow>{$latency}ms</>");
            $this->newLine();
            $this->line('  ─────────────────────────────────────');
            $this->line('  <fg=cyan;options=bold>Response:</>');
            $this->line('  '.str_replace("\n", "\n  ", trim($response['text'])));
            $this->newLine();
            $this->line('  <fg=cyan;options=bold>Token usage:</>');
            $this->line('    Input  : '.$response['usage']['input_tokens']);
            $this->line('    Output : '.$response['usage']['output_tokens']);
            $this->line('    Total  : '.$response['usage']['total_tokens']);
            $this->newLine();
            $this->line('  <fg=green;options=bold>✔ All checks passed. Ollama is ready.</>');
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->line("  <fg=red>[FAIL]</> Chat request failed: {$e->getMessage()}");
            $this->newLine();

            return self::FAILURE;
        }
    }
}
