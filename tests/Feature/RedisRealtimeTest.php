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
}
