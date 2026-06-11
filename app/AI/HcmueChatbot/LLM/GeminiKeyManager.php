<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiKeyManager
{
    /**
     * @var array<string>
     */
    protected array $overrideKeys = [];

    /**
     * GeminiKeyManager constructor.
     *
     * @param  array<string>|null  $overrideKeys
     */
    public function __construct(?array $overrideKeys = null)
    {
        if ($overrideKeys !== null) {
            $this->overrideKeys = array_values(array_unique(array_filter($overrideKeys)));
        }
    }

    /**
     * Get all raw keys configured in the system.
     *
     * @return array<string>
     */
    public function getKeys(): array
    {
        if (! empty($this->overrideKeys)) {
            return $this->overrideKeys;
        }

        $keys = [];

        // Primary key from config
        $primary = config('ai.gemini.api_key');
        if ($primary) {
            $keys[] = $primary;
        }

        // Fallbacks from config list
        $fallbacks = config('ai.gemini.api_keys');
        if (is_array($fallbacks)) {
            foreach ($fallbacks as $key) {
                if ($key) {
                    $keys[] = $key;
                }
            }
        }

        // Fallback services key if present
        $servicesKey = config('services.gemini.key');
        if ($servicesKey) {
            $keys[] = $servicesKey;
        }

        return array_values(array_unique(array_filter($keys)));
    }

    /**
     * Run a callback using the best available Gemini API key.
     *
     * @template T
     *
     * @param  callable(string): T  $callback
     * @return T
     *
     * @throws \Exception
     */
    public function run(callable $callback, int $maxAttempts = 5): mixed
    {
        $candidates = $this->getCandidateKeys();

        if (empty($candidates)) {
            Log::error('Gemini KeyManager: No configured or healthy API keys available.');
            throw new \Exception('No Gemini API keys configured.');
        }

        // Limit attempts to min($maxAttempts, count($candidates))
        $attemptsLimit = min($maxAttempts, count($candidates));
        $lastException = null;

        for ($attempt = 1; $attempt <= $attemptsLimit; $attempt++) {
            $candidate = $candidates[$attempt - 1];
            $key = $candidate['key'];
            $hash = $candidate['hash'];

            // Mark key in flight
            $this->incrementInFlight($hash);

            try {
                // Execute callback
                $result = $callback($key);

                // Success!
                $this->handleSuccess($key);

                return $result;
            } catch (\Throwable $e) {
                $lastException = $e;

                // Handle failure (cooldown / status update)
                $this->handleFailure($key, $e);
            } finally {
                // Decrement in flight
                $this->decrementInFlight($hash);
            }
        }

        Log::error('Gemini KeyManager: All candidate keys failed or are cooling down.');
        throw new \Exception(
            'All Gemini API keys are currently unavailable or cooling down. Last error: '.
            ($lastException ? $lastException->getMessage() : 'Unknown error'),
            0,
            $lastException
        );
    }

    /**
     * Get candidate keys sorted by their runtime health state and in-flight count.
     *
     * @return array<array{key: string, hash: string, masked: string, state: array, in_flight: int}>
     */
    public function getCandidateKeys(): array
    {
        $rawKeys = $this->getKeys();
        $candidates = [];

        foreach ($rawKeys as $key) {
            $hash = md5($key);
            $state = $this->getKeyState($hash);
            $inFlight = $this->getInFlightCount($hash);

            $candidates[] = [
                'key' => $key,
                'hash' => $hash,
                'masked' => $this->maskKey($key),
                'state' => $state,
                'in_flight' => $inFlight,
            ];
        }

        $now = now();
        $groupA = []; // Healthy keys: not cooling down, status is not forbidden
        $groupB = []; // Cooling down keys: currently cooling down, status is not forbidden

        foreach ($candidates as $c) {
            $status = $c['state']['status'];

            // Explicitly exclude forbidden/invalid keys
            if ($status === 'forbidden') {
                continue;
            }

            $cooldownUntil = $c['state']['cooldown_until'];
            $isCoolingDown = $cooldownUntil !== null && strtotime($cooldownUntil) > $now->getTimestamp();

            if ($isCoolingDown) {
                $groupB[] = $c;
            } else {
                $groupA[] = $c;
            }
        }

        // Sort Group A (Healthy):
        // 1. Prioritize lower in_flight count.
        // 2. Prioritize oldest last_success_at (least recently used) or nulls first.
        usort($groupA, function ($a, $b) {
            if ($a['in_flight'] !== $b['in_flight']) {
                return $a['in_flight'] <=> $b['in_flight'];
            }
            $aTime = $a['state']['last_success_at'] ? strtotime($a['state']['last_success_at']) : 0;
            $bTime = $b['state']['last_success_at'] ? strtotime($b['state']['last_success_at']) : 0;

            return $aTime <=> $bTime;
        });

        // Sort Group B (Cooling down):
        // Prioritize keys whose cooldown finishes first.
        usort($groupB, function ($a, $b) {
            $aTime = $a['state']['cooldown_until'] ? strtotime($a['state']['cooldown_until']) : 0;
            $bTime = $b['state']['cooldown_until'] ? strtotime($b['state']['cooldown_until']) : 0;

            return $aTime <=> $bTime;
        });

        return array_merge($groupA, $groupB);
    }

    /**
     * Get the runtime health state of a key by hash.
     *
     * @return array{status: string, fail_count: int, last_failed_at: ?string, cooldown_until: ?string, last_success_at: ?string}
     */
    public function getKeyState(string $hash): array
    {
        return Cache::get("gemini:key_state:{$hash}", [
            'status' => 'healthy',
            'fail_count' => 0,
            'last_failed_at' => null,
            'cooldown_until' => null,
            'last_success_at' => null,
        ]);
    }

    /**
     * Set the runtime health state of a key by hash (useful for testing).
     */
    public function setKeyState(string $hash, array $state): void
    {
        Cache::put("gemini:key_state:{$hash}", $state, now()->addDay());
    }

    /**
     * Clear key states (useful for testing).
     */
    public function clearKeyStates(): void
    {
        foreach ($this->getKeys() as $key) {
            $hash = md5($key);
            Cache::forget("gemini:key_state:{$hash}");
            Cache::forget("gemini:key_inflight:{$hash}");
        }
    }

    /**
     * Get the in-flight count of a key by hash.
     */
    public function getInFlightCount(string $hash): int
    {
        return (int) Cache::get("gemini:key_inflight:{$hash}", 0);
    }

    /**
     * Increment the in-flight count of a key.
     */
    public function incrementInFlight(string $hash): void
    {
        try {
            Cache::increment("gemini:key_inflight:{$hash}");
        } catch (\Throwable $e) {
            // Fallback for cache drivers that don't support atomics or if it's not set
            $val = (int) Cache::get("gemini:key_inflight:{$hash}", 0);
            Cache::put("gemini:key_inflight:{$hash}", $val + 1, now()->addMinutes(5));
        }
    }

    /**
     * Decrement the in-flight count of a key.
     */
    public function decrementInFlight(string $hash): void
    {
        try {
            Cache::decrement("gemini:key_inflight:{$hash}");
            $val = (int) Cache::get("gemini:key_inflight:{$hash}", 0);
            if ($val < 0) {
                Cache::put("gemini:key_inflight:{$hash}", 0, now()->addMinutes(5));
            }
        } catch (\Throwable $e) {
            $val = (int) Cache::get("gemini:key_inflight:{$hash}", 0);
            Cache::put("gemini:key_inflight:{$hash}", max(0, $val - 1), now()->addMinutes(5));
        }
    }

    /**
     * Handle a successful request with a key.
     */
    protected function handleSuccess(string $key): void
    {
        $hash = md5($key);
        $state = $this->getKeyState($hash);

        $state['status'] = 'healthy';
        $state['fail_count'] = 0;
        $state['cooldown_until'] = null;
        $state['last_success_at'] = now()->toIso8601String();

        Cache::put("gemini:key_state:{$hash}", $state, now()->addDay());
    }

    /**
     * Handle a failed request with a key.
     */
    protected function handleFailure(string $key, \Throwable $e): void
    {
        $hash = md5($key);
        $state = $this->getKeyState($hash);

        $state['fail_count']++;
        $state['last_failed_at'] = now()->toIso8601String();

        $statusCode = null;
        $errorBody = '';

        if ($e instanceof RequestException) {
            $statusCode = $e->response->status();
            $errorBody = $e->response->body();
        } elseif (method_exists($e, 'getResponse') && $e->getResponse()) {
            $response = $e->getResponse();
            if (method_exists($response, 'getStatusCode')) {
                $statusCode = $response->getStatusCode();
            }
            if (method_exists($response, 'getBody') && $response->getBody()) {
                $errorBody = (string) $response->getBody();
            }
        }

        $cooldownSeconds = 0;

        if ($statusCode === 429) {
            $state['status'] = 'rate_limited';

            // Check Retry-After
            $retryAfter = null;
            if ($e instanceof RequestException && $e->response->header('Retry-After')) {
                $retryAfter = (int) $e->response->header('Retry-After');
            }

            if ($retryAfter) {
                $cooldownSeconds = $retryAfter;
            } else {
                // Exponential backoff: 60s, 120s, 300s, max 600s
                $cooldownSeconds = match ($state['fail_count']) {
                    1 => 60,
                    2 => 120,
                    default => 300,
                };
            }

            $state['cooldown_until'] = now()->addSeconds($cooldownSeconds)->toIso8601String();

            $maskedKey = $this->maskKey($key);
            Log::warning("Gemini KeyManager: Key [{$maskedKey}] rate limited (429). Cooling down for {$cooldownSeconds}s. Fail count: {$state['fail_count']}. Body: {$errorBody}");

        } elseif ($statusCode === 403) {
            if (str_contains(strtolower($errorBody), 'quota') || str_contains(strtolower($errorBody), 'limit')) {
                $state['status'] = 'exhausted';
                $cooldownSeconds = 3600; // 1 hour
                $state['cooldown_until'] = now()->addSeconds($cooldownSeconds)->toIso8601String();
                $maskedKey = $this->maskKey($key);
                Log::warning("Gemini KeyManager: Key [{$maskedKey}] exhausted (403 quota). Cooling down for {$cooldownSeconds}s. Body: {$errorBody}");
            } else {
                $state['status'] = 'forbidden';
                $cooldownSeconds = 86400; // 24 hours
                $state['cooldown_until'] = now()->addSeconds($cooldownSeconds)->toIso8601String();
                $maskedKey = $this->maskKey($key);
                Log::error("Gemini KeyManager: Key [{$maskedKey}] forbidden/permission issue (403). Cooldown for 24h. Body: {$errorBody}");
            }

        } elseif ($statusCode === 401) {
            $state['status'] = 'forbidden';
            $cooldownSeconds = 86400; // 24 hours
            $state['cooldown_until'] = now()->addSeconds($cooldownSeconds)->toIso8601String();
            $maskedKey = $this->maskKey($key);
            Log::error("Gemini KeyManager: Key [{$maskedKey}] unauthorized/invalid (401). Cooldown for 24h. Body: {$errorBody}");

        } else {
            // Other/network error (5xx, ConnectionException)
            $state['status'] = 'unknown';
            $cooldownSeconds = 5; // very short cooldown
            $state['cooldown_until'] = now()->addSeconds($cooldownSeconds)->toIso8601String();
            $maskedKey = $this->maskKey($key);
            Log::warning("Gemini KeyManager: Key [{$maskedKey}] failed with error ({$statusCode}): ".$e->getMessage().'. Cooling down for 5s.');
        }

        Cache::put("gemini:key_state:{$hash}", $state, now()->addSeconds(max($cooldownSeconds, 60)));
    }

    /**
     * Mask API key for logs.
     */
    public function maskKey(string $key): string
    {
        if (strlen($key) <= 8) {
            return '***';
        }

        return substr($key, 0, 4).'...'.substr($key, -4);
    }
}
