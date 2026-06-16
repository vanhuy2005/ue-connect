<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RedisRealtimeTest extends TestCase
{
    public function test_redis_config_resolves_without_exception()
    {
        $redisConfig = config('database.redis');

        $this->assertNotNull($redisConfig);
        $this->assertArrayHasKey('default', $redisConfig);
        $this->assertArrayHasKey('cache', $redisConfig);
    }

    public function test_cache_store_can_be_set_to_redis_through_config()
    {
        Config::set('cache.default', 'redis');

        $this->assertEquals('redis', config('cache.default'));
        $this->assertNotNull(config('cache.stores.redis'));
        $this->assertEquals('redis', config('cache.stores.redis.driver'));
    }

    public function test_queue_default_connection_can_be_redis()
    {
        Config::set('queue.default', 'redis');

        $this->assertEquals('redis', config('queue.default'));
        $this->assertNotNull(config('queue.connections.redis'));
        $this->assertEquals('redis', config('queue.connections.redis.driver'));
    }

    public function test_existing_database_queue_config_remains_available()
    {
        $this->assertNotNull(config('queue.connections.database'));
        $this->assertEquals('database', config('queue.connections.database.driver'));
    }

    public function test_reverb_client_configuration_exists_and_defaults_correctly()
    {
        $clientConfig = config('broadcasting.connections.reverb.client');

        $this->assertNotNull($clientConfig);
        $this->assertArrayHasKey('host', $clientConfig);
        $this->assertArrayHasKey('port', $clientConfig);
        $this->assertArrayHasKey('scheme', $clientConfig);
    }

    public function test_pages_loading_app_js_contain_realtime_meta_tags()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('name="reverb-app-key"', false);
        $response->assertSee('name="reverb-host"', false);
        $response->assertSee('name="reverb-port"', false);
        $response->assertSee('name="reverb-scheme"', false);
    }
}
