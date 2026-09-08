<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/Core/JsonStore.php';
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Models/Request.php';
require_once __DIR__ . '/Models/Stock.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/PortalController.php';

use BloodBridge\Controllers\AuthController;
use BloodBridge\Controllers\PortalController;
use BloodBridge\Core\JsonStore;
use BloodBridge\Models\Request;
use BloodBridge\Models\Stock;
use BloodBridge\Models\User;

$store = new JsonStore(dirname(__DIR__) . '/data'); $userModel = new User($store); $requestModel = new Request($store); $stockModel = new Stock($store); $authController = new AuthController($userModel); $portalController = new PortalController($userModel, $requestModel, $stockModel);
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url = 'index.php'): never { header('Location: ' . $url); exit; }
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function take_flash(): ?array { $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $flash; }
function new_id(string $prefix): string { return $prefix . '-' . strtoupper(bin2hex(random_bytes(3))); }
function status_class(string $status): string { return strtolower(str_replace(' ', '-', $status)); }
function valid_blood_group(string $group): string { return in_array($group, Stock::groups(), true) ? $group : 'O+'; }
function valid_urgency(string $urgency): string { return in_array($urgency, ['Routine', 'Urgent', 'Critical'], true) ? $urgency : 'Routine'; }
function current_user(User $users): ?array { return isset($_SESSION['user_id']) ? $users->find((string) $_SESSION['user_id']) : null; }
function require_login(User $users): array { $user = current_user($users); if (!$user) redirect('index.php?page=login'); return $user; }
function require_role(array $user, string $role): void { if (($user['role'] ?? '') !== $role) { flash('You do not have permission to perform that action.', 'error'); redirect('index.php'); } }
function verify_csrf(array $input): void { if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), (string) ($input['csrf'] ?? ''))) { http_response_code(419); exit('Invalid form token. Please refresh and try again.'); } }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function render(string $view, array $data = []): void
{
	extract($data, EXTR_SKIP);
	ob_start();
	include dirname(__DIR__) . '/views/' . $view . '.php';
	$output = (string) ob_get_clean();
	if ($view !== 'header' && $view !== 'footer') {
		$token = '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
		$output = (string) preg_replace('/<form method="post([^>]*)>/', '<form method="post$1">' . $token, $output);
	}
	echo $output;
}