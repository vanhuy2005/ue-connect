<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class IconRegistryTest extends TestCase
{
    public function test_static_blade_icon_names_are_registered(): void
    {
        $iconComponent = File::get(resource_path('views/components/ui/icon.blade.php'));
        preg_match_all("/@case\\('([^']+)'\\)/", $iconComponent, $caseMatches);
        $registeredIcons = collect($caseMatches[1])->unique();

        $bladeIcons = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->flatMap(function ($file) {
                $contents = File::get($file->getRealPath());
                preg_match_all('/<x-ui\\.icon[^>]*name="([a-z0-9\\-]+)"/', $contents, $componentMatches);
                preg_match_all('/\\bicon="([a-z0-9\\-]+)"/', $contents, $propMatches);

                return array_merge($componentMatches[1], $propMatches[1]);
            })
            ->unique()
            ->values();

        $missingIcons = $bladeIcons->reject(fn (string $icon) => $registeredIcons->contains($icon))->values();

        $this->assertTrue($missingIcons->isEmpty(), 'Missing icons: '.$missingIcons->implode(', '));
    }
}
