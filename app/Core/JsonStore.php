<?php
declare(strict_types=1);

namespace BloodBridge\Core;

final class JsonStore
{
    public function __construct(private readonly string $directory) {}

    public function all(string $collection): array
    {
        $path = $this->path($collection);
        if (!is_file($path)) return [];
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    public function replace(string $collection, array $records): void
    {
        $json = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($this->path($collection), $json . PHP_EOL, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to save data. Check the data directory permissions.');
        }
    }

    private function path(string $collection): string
    {
        return $this->directory . '/' . preg_replace('/[^a-z0-9_-]/i', '', $collection) . '.json';
    }
}