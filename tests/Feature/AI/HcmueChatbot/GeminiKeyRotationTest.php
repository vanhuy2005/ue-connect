<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\LLM\GeminiKeyManager;
use App\AI\HcmueChatbot\LLM\GeminiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiKeyRotationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure dummy keys
        config([
            'ai.gemini.api_key' => 'PRIMARY_KEY_12345',
            'ai.gemini.api_keys' => [
                'FALLBACK_KEY_1',
                'FALLBACK_KEY_2',
                'FALLBACK_KEY_3',
            ],
        ]);

        $keyManager = new GeminiKeyManager;
        $keyManager->clearKeyStates();
    }

    public function test_key_manager_loads_all_keys(): void
    {
        $keyManager = new GeminiKeyManager;
        $keys = $keyManager->getKeys();

        $this->assertContains('PRIMARY_KEY_12345', $keys);
        $this->assertContains('FALLBACK_KEY_1', $keys);
        $this->assertContains('FALLBACK_KEY_2', $keys);
        $this->assertContains('FALLBACK_KEY_3', $keys);
    }

    public function test_key_manager_filters_out_duplicates_and_empty(): void
    {
        config([
            'ai.gemini.api_key' => 'PRIMARY_KEY_12345',
            'ai.gemini.api_keys' => [
                'PRIMARY_KEY_12345',
                '',
                'FALLBACK_KEY_1',
                null,
                'FALLBACK_KEY_1',
            ],
        ]);

        $keyManager = new GeminiKeyManager;
        $keys = $keyManager->getKeys();

        $this->assertCount(2, $keys);
        $this->assertSame(['PRIMARY_KEY_12345', 'FALLBACK_KEY_1'], $keys);
    }

    public function test_cooldown_excludes_rate_limited_keys_temporarily(): void
    {
        $keyManager = new GeminiKeyManager;

        // Mark PRIMARY_KEY_12345 as rate_limited under cooldown
        $hashPrimary = md5('PRIMARY_KEY_12345');
        $keyManager->setKeyState($hashPrimary, [
            'status' => 'rate_limited',
            'fail_count' => 1,
            'last_failed_at' => now()->toIso8601String(),
            'cooldown_until' => now()->addMinutes(5)->toIso8601String(),
            'last_success_at' => null,
        ]);

        $candidates = $keyManager->getCandidateKeys();

        // The rate limited key should be placed last (since it is cooling down)
        $this->assertNotEmpty($candidates);

        // Find position of PRIMARY_KEY_12345
        $foundIndex = null;
        foreach ($candidates as $index => $c) {
            if ($c['key'] === 'PRIMARY_KEY_12345') {
                $foundIndex = $index;
            }
        }

        // It must be at the very end of candidates list since other keys are healthy
        $this->assertSame(count($candidates) - 1, $foundIndex);
    }

    public function test_429_triggers_cooldown_and_fails_over_to_next_key(): void
    {
        // Mock API responses
        // 1st request with PRIMARY_KEY_12345 gets 429
        // 2nd request with FALLBACK_KEY_1 succeeds with 200
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=PRIMARY_KEY_12345' => Http::response(['error' => 'Rate limit exceeded'], 429),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=FALLBACK_KEY_1' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Success from fallback 1']]]],
                ],
                'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 5, 'totalTokenCount' => 15],
            ], 200),
        ]);

        $provider = new GeminiProvider;
        $result = $provider->generate('Hello');

        $this->assertSame('Success from fallback 1', $result['text']);

        // Check if PRIMARY_KEY_12345 state in cache is updated to rate_limited and cooldown is set
        $hashPrimary = md5('PRIMARY_KEY_12345');
        $state = $provider->getKeyManager()->getKeyState($hashPrimary);

        $this->assertSame('rate_limited', $state['status']);
        $this->assertGreaterThan(0, $state['fail_count']);
        $this->assertNotNull($state['cooldown_until']);
    }

    public function test_401_triggers_forbidden_and_excludes_key_from_rotation(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=PRIMARY_KEY_12345' => Http::response(['error' => 'API Key Invalid'], 401),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=FALLBACK_KEY_1' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Success from fallback 1']]]],
                ],
                'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 5, 'totalTokenCount' => 15],
            ], 200),
        ]);

        $provider = new GeminiProvider;
        $result = $provider->generate('Hello');

        $this->assertSame('Success from fallback 1', $result['text']);

        $hashPrimary = md5('PRIMARY_KEY_12345');
        $state = $provider->getKeyManager()->getKeyState($hashPrimary);

        $this->assertSame('forbidden', $state['status']);

        // Candidates must NOT contain the forbidden key at all
        $candidates = $provider->getKeyManager()->getCandidateKeys();
        foreach ($candidates as $c) {
            $this->assertNotSame('PRIMARY_KEY_12345', $c['key']);
        }
    }

    public function test_all_keys_failing_throws_proper_exception(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Some API Error'], 429),
        ]);

        $provider = new GeminiProvider;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('All Gemini API keys are currently unavailable or cooling down.');

        $provider->generate('Hello');
    }

    public function test_in_flight_count_increments_and_decrements(): void
    {
        $keyManager = new GeminiKeyManager(['MY_TEST_KEY']);
        $hash = md5('MY_TEST_KEY');
        $keyManager->clearKeyStates();

        $this->assertSame(0, $keyManager->getInFlightCount($hash));

        $keyManager->run(function (string $key) use ($keyManager, $hash) {
            $this->assertSame(1, $keyManager->getInFlightCount($hash));

            return 'done';
        });

        $this->assertSame(0, $keyManager->getInFlightCount($hash));
    }
}
