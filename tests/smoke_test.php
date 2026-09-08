<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$donations = file_get_contents($root . '/views/donations.php');
$router = file_get_contents($root . '/index.php');

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

check($donations !== false, 'Donations view could not be read.');
check($router !== false, 'Router could not be read.');
check(str_contains($donations, '$requests = $requests ?? [];'), 'Donations view must tolerate missing request data.');
check(str_contains($donations, "['Pending','Donor matched']"), 'Donations view must show open requests only.');
check(str_contains($donations, 'name="action" value="accept_request"'), 'Donations view must submit the accept action.');
check(str_contains($router, '$pagesByRole = ['), 'Router must define role-specific pages.');
check(str_contains($router, "'admin' => ['dashboard', 'profile', 'admin-requests', 'stock']"), 'Admin pages must remain restricted to admins.');
check(str_contains($router, "'donor' => ['dashboard', 'profile', 'donations']"), 'Donor pages must include donation requests.');

echo "Smoke test passed.\n";