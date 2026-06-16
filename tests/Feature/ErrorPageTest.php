<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    /**
     * Test that a non-existent route returns 404 and uses the custom 404 page.
     */
    public function test_non_existent_route_renders_custom_404_page(): void
    {
        $response = $this->get('/this-route-does-not-exist-at-all-123456');

        $response->assertStatus(404);
        $response->assertSee('Không tìm thấy trang');
        $response->assertSee('Về trang chủ');
    }
}
