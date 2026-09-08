<?php
declare(strict_types=1);

namespace BloodBridge\Models;

use BloodBridge\Core\JsonStore;

final class User
{
    public function __construct(private readonly JsonStore $store) {}
    public function all(): array { return $this->store->all('users'); }
    public function find(string $id): ?array { foreach ($this->all() as $user) if (($user['id'] ?? '') === $id) return $user; return null; }
    public function findByEmail(string $email): ?array { foreach ($this->all() as $user) if (strcasecmp((string) ($user['email'] ?? ''), $email) === 0) return $user; return null; }
    public function create(array $attributes): array { $users = $this->all(); $user = array_merge(['id' => new_id('USR'), 'joined' => date('Y-m-d')], $attributes); $users[] = $user; $this->store->replace('users', $users); return $user; }
    public function update(string $id, callable $mutator): array { $users = $this->all(); foreach ($users as &$user) if (($user['id'] ?? '') === $id) { $user = $mutator($user); $this->store->replace('users', $users); return $user; } throw new \RuntimeException('User not found.'); }
}