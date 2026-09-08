<?php
declare(strict_types=1);

namespace BloodBridge\Controllers;

use BloodBridge\Models\User;

final class AuthController
{
    public function __construct(private readonly User $users) {}

    public function login(array $input): never
    {
        $user = $this->users->findByEmail(strtolower(trim((string) ($input['email'] ?? ''))));
        if (!$user || !password_verify((string) ($input['password'] ?? ''), (string) ($user['password'] ?? ''))) { flash('Email or password is incorrect.', 'error'); redirect('index.php?page=login'); }
        session_regenerate_id(true); $_SESSION['user_id'] = $user['id']; redirect('index.php');
    }

    public function register(array $input): never
    {
        $email = strtolower(trim((string) ($input['email'] ?? ''))); $name = trim((string) ($input['name'] ?? '')); $password = (string) ($input['password'] ?? ''); $role = (string) ($input['role'] ?? 'recipient');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || strlen($password) < 6 || !in_array($role, ['donor', 'recipient'], true)) { flash('Please complete all fields. Passwords must be at least 6 characters.', 'error'); redirect('index.php?page=register'); }
        if ($this->users->findByEmail($email)) { flash('An account with this email already exists.', 'error'); redirect('index.php?page=register'); }
        $this->users->create(['name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role, 'blood_group' => valid_blood_group($input['blood_group'] ?? ''), 'phone' => trim((string) ($input['phone'] ?? '')), 'location' => trim((string) ($input['location'] ?? '')), 'available' => $role === 'donor']);
        flash('Account created. Welcome to BloodBridge.'); redirect('index.php?page=login');
    }
}