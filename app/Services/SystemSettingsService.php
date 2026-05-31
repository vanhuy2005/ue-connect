<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SystemSettingsService
{
    protected string $path = 'system_settings.json';

    protected string $snapshotsDir = 'system_settings_snapshots';

    public function getAll(): array
    {
        if (! Storage::exists($this->path)) {
            return [];
        }

        $json = Storage::get($this->path);

        return json_decode($json, true) ?? [];
    }

    public function setAll(array $data): void
    {
        Storage::put($this->path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function saveSnapshot(?string $name = null): string
    {
        $name = $name ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $name) : null;
        $fileName = ($name ?? 'snapshot_'.date('Ymd_His')).'.json';

        $content = Storage::exists($this->path) ? Storage::get($this->path) : json_encode([]);

        $full = $this->snapshotsDir.'/'.$fileName;
        Storage::put($full, $content);

        return $fileName;
    }

    public function listSnapshots(): array
    {
        if (! Storage::exists($this->snapshotsDir)) {
            return [];
        }

        $files = Storage::files($this->snapshotsDir);
        // Return names sorted desc
        rsort($files);

        return array_map(function ($f) {
            return basename($f);
        }, $files);
    }

    public function getSnapshotContent(string $file): ?string
    {
        $path = $this->snapshotsDir.'/'.$file;
        if (! Storage::exists($path)) {
            return null;
        }

        return Storage::get($path);
    }

    public function restoreSnapshot(string $file): bool
    {
        $content = $this->getSnapshotContent($file);
        if (! $content) {
            return false;
        }

        Storage::put($this->path, $content);

        return true;
    }
}
