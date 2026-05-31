<?php

namespace Tests\Unit\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IconComponentTest extends TestCase
{
    public function test_admin_sidebar_icon_aliases_render_expected_svg_markup(): void
    {
        $iconSnippets = [
            'building-2' => '<rect x="3" y="3" width="7" height="7"></rect>',
            'key-round' => '<path d="m21 2-2 2m-7.61 7.61',
            'history' => '<path d="M12 6v6l4 2"></path>',
            'bar-chart-3' => '<rect x="16" y="4" width="3" height="16" rx="1"></rect>',
            'settings-2' => '<path d="M19.4 15a1.65',
        ];

        foreach ($iconSnippets as $name => $expectedSnippet) {
            $html = Blade::render('<x-ui.icon :name="$name" />', ['name' => $name]);

            $this->assertStringContainsString($expectedSnippet, $html, "Icon [{$name}] did not render the expected SVG markup.");
            $this->assertStringNotContainsString('stroke="#EF4444"', $html, "Icon [{$name}] fell back to the warning icon.");
        }
    }
}
