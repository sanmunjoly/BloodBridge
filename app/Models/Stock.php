<?php
declare(strict_types=1);

namespace BloodBridge\Models;

use BloodBridge\Core\JsonStore;

final class Stock
{
    private const GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    public function __construct(private readonly JsonStore $store) {}
    public function all(): array { return array_replace(array_fill_keys(self::GROUPS, 0), $this->store->all('stock')); }
    public function set(string $group, int $units): void { if (!in_array($group, self::GROUPS, true)) throw new \InvalidArgumentException('Invalid blood group.'); $stock = $this->all(); $stock[$group] = max(0, $units); $this->store->replace('stock', $stock); }
    public static function groups(): array { return self::GROUPS; }
}