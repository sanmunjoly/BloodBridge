<?php
declare(strict_types=1);

namespace BloodBridge\Models;

use BloodBridge\Core\JsonStore;

final class Request
{
    public function __construct(private readonly JsonStore $store) {}
    public function all(): array { return $this->store->all('requests'); }
    public function forRecipient(string $recipientId): array { return array_values(array_filter($this->all(), fn(array $request): bool => ($request['recipient_id'] ?? '') === $recipientId)); }
    public function open(): array { return array_values(array_filter($this->all(), fn(array $request): bool => in_array($request['status'] ?? '', ['Pending', 'Donor matched'], true))); }
    public function create(array $attributes): array { $requests = $this->all(); $request = array_merge(['id' => new_id('REQ'), 'status' => 'Pending', 'created' => date('Y-m-d')], $attributes); $requests[] = $request; $this->store->replace('requests', $requests); return $request; }
    public function update(string $id, callable $mutator): array { $requests = $this->all(); foreach ($requests as &$request) if (($request['id'] ?? '') === $id) { $request = $mutator($request); $this->store->replace('requests', $requests); return $request; } throw new \RuntimeException('Request not found.'); }
}