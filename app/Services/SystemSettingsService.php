<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SystemSettingsService
{
    protected string $path = 'system_settings.json';

    protected string $snapshotsDir = 'system_settings_snapshots';

    public function getAll(): array
    {
        if (! Storage::disk('local')->exists($this->path)) {
            return [];
        }

        $json = Storage::disk('local')->get($this->path);

        return json_decode($json, true) ?? [];
    }

    public function setAll(array $data): void
    {
        Storage::disk('local')->put($this->path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function saveSnapshot(?string $name = null): string
    {
        $name = $name ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $name) : null;
        $fileName = ($name ?? 'snapshot_'.date('Ymd_His')).'.json';

        $content = Storage::disk('local')->exists($this->path) ? Storage::disk('local')->get($this->path) : json_encode([]);

        $full = $this->snapshotsDir.'/'.$fileName;
        Storage::disk('local')->put($full, $content);

        return $fileName;
    }

    public function listSnapshots(): array
    {
        if (! Storage::disk('local')->exists($this->snapshotsDir)) {
            return [];
        }

        $files = Storage::disk('local')->files($this->snapshotsDir);
        // Return names sorted desc
        rsort($files);

        return array_map(function ($f) {
            return basename($f);
        }, $files);
    }

    public function getSnapshotContent(string $file): ?string
    {
        $path = $this->snapshotsDir.'/'.$file;
        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    public function restoreSnapshot(string $file): bool
    {
        $content = $this->getSnapshotContent($file);
        if (! $content) {
            return false;
        }

        Storage::disk('local')->put($this->path, $content);

        return true;
    }
}
