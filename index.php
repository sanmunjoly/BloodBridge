<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$page = (string) ($_GET['page'] ?? 'dashboard');
$user = current_user($userModel);

if ($page === 'logout') {
    session_destroy();
    redirect('index.php?page=login');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf($_POST);
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'login') $authController->login($_POST);
        if ($action === 'register') $authController->register($_POST);
        $user = require_login($userModel);
        match ($action) {
            'profile' => $portalController->updateProfile($user, $_POST),
            'availability' => $portalController->setAvailability($user, $_POST),
            'request' => $portalController->createRequest($user, $_POST),
            'accept_request' => $portalController->acceptRequest($user, $_POST),
            'admin_request' => $portalController->updateRequest($user, $_POST),
            'stock' => $portalController->updateStock($user, $_POST),
            default => throw new InvalidArgumentException('Unknown action.')
        };
    }

    if (in_array($page, ['login', 'register'], true)) {
        render('auth', ['page' => $page]);
        exit;
    }

    $user = require_login($userModel);
    $pagesByRole = [
        'recipient' => ['dashboard', 'profile', 'requests', 'find-donor'],
        'donor' => ['dashboard', 'profile', 'donations'],
        'admin' => ['dashboard', 'profile', 'admin-requests', 'stock'],
    ];
    $allowedPages = $pagesByRole[$user['role'] ?? ''] ?? [];
    if (!in_array($page, $allowedPages, true)) $page = 'dashboard';
    $data = $page === 'dashboard' ? $portalController->dashboard($user) : $portalController->page($page, $user);
    $data['page'] = $page;
    $data['flash'] = take_flash();
    render('header', $data);
    render($page === 'dashboard' ? 'dashboard' : $page, $data);
    render('footer', $data);
} catch (Throwable $error) {
    http_response_code(400);
    flash($error->getMessage(), 'error');
    redirect($user ? 'index.php' : 'index.php?page=login');
}