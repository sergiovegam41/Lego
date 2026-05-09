<?php

namespace Core\Services\Graph;

class DescriptionCache
{
    private array $entries = [];

    public function __construct(private readonly string $cachePath)
    {
        $this->load();
    }

    public function get(string $file, string $hash): ?string
    {
        $entry = $this->entries[$file] ?? null;
        if ($entry === null) {
            return null;
        }
        if (($entry['hash'] ?? null) !== $hash) {
            return null;
        }
        return $entry['description'] ?? null;
    }

    public function put(string $file, string $hash, string $description, string $model): void
    {
        $this->entries[$file] = [
            'hash'        => $hash,
            'description' => $description,
            'model'       => $model,
            'updated_at'  => date('c'),
        ];
    }

    public function save(): void
    {
        $dir = dirname($this->cachePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        ksort($this->entries);
        file_put_contents(
            $this->cachePath,
            json_encode($this->entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    public function size(): int
    {
        return count($this->entries);
    }

    private function load(): void
    {
        if (!file_exists($this->cachePath)) {
            return;
        }
        $raw = file_get_contents($this->cachePath);
        $data = json_decode($raw, true);
        if (is_array($data)) {
            $this->entries = $data;
        }
    }
}
