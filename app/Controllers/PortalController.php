<?php
declare(strict_types=1);

namespace BloodBridge\Controllers;

use BloodBridge\Models\Request;
use BloodBridge\Models\Stock;
use BloodBridge\Models\User;

final class PortalController
{
    public function __construct(private readonly User $users, private readonly Request $requests, private readonly Stock $stock) {}
    public function dashboard(array $user): array { return $this->shared($user); }
    public function page(string $page, array $user): array { $data = $this->shared($user); if ($page === 'requests') $data['my_requests'] = $this->requests->forRecipient($user['id']); if ($page === 'donations') $data['open_requests'] = $this->requests->open(); if ($page === 'find-donor') $data['donors'] = array_values(array_filter($this->users->all(), fn(array $candidate): bool => ($candidate['role'] ?? '') === 'donor' && !empty($candidate['available']))); if ($page === 'stock') $data['stock'] = $this->stock->all(); return $data; }
    public function updateProfile(array $user, array $input): never { $this->users->update($user['id'], fn(array $record): array => array_merge($record, ['name' => trim((string) ($input['name'] ?? $record['name'])), 'phone' => trim((string) ($input['phone'] ?? '')), 'location' => trim((string) ($input['location'] ?? '')), 'blood_group' => valid_blood_group($input['blood_group'] ?? $record['blood_group'])])); flash('Profile updated.'); redirect('index.php?page=profile'); }
    public function setAvailability(array $user, array $input): never { require_role($user, 'donor'); $this->users->update($user['id'], fn(array $record): array => array_merge($record, ['available' => ($input['available'] ?? '') === '1'])); flash('Availability status updated.'); redirect('index.php?page=profile'); }
    public function createRequest(array $user, array $input): never { require_role($user, 'recipient'); $hospital = trim((string) ($input['hospital'] ?? '')); if ($hospital === '') { flash('Hospital or location is required.', 'error'); redirect('index.php?page=requests'); } $this->requests->create(['recipient_id' => $user['id'], 'recipient' => $user['name'], 'blood_group' => valid_blood_group($input['blood_group'] ?? ''), 'units' => min(20, max(1, (int) ($input['units'] ?? 1))), 'hospital' => $hospital, 'urgency' => valid_urgency($input['urgency'] ?? ''), 'note' => trim((string) ($input['note'] ?? ''))]); flash('Blood request submitted for review.'); redirect('index.php?page=requests'); }
    public function acceptRequest(array $user, array $input): never { require_role($user, 'donor'); $id = trim((string) ($input['request_id'] ?? '')); $this->requests->update($id, function (array $request) use ($user): array { if (($request['status'] ?? '') !== 'Pending') throw new \InvalidArgumentException('This request is no longer available.'); return array_merge($request, ['status' => 'Donor matched', 'donor_id' => $user['id'], 'donor' => $user['name']]); }); flash('You accepted this donation request.'); redirect('index.php?page=donations'); }
    public function updateRequest(array $user, array $input): never { require_role($user, 'admin'); $status = (string) ($input['status'] ?? ''); if (!in_array($status, ['Pending', 'Approved', 'Fulfilled', 'Rejected'], true)) throw new \InvalidArgumentException('Invalid request status.'); $this->requests->update((string) ($input['request_id'] ?? ''), fn(array $request): array => array_merge($request, ['status' => $status])); flash('Request status updated.'); redirect('index.php?page=admin-requests'); }
    public function updateStock(array $user, array $input): never { require_role($user, 'admin'); $this->stock->set((string) ($input['blood_group'] ?? ''), (int) ($input['units'] ?? 0)); flash('Blood stock updated.'); redirect('index.php?page=stock'); }
    private function shared(array $user): array { return ['user' => $user, 'requests' => $this->requests->all(), 'stock' => $this->stock->all(), 'all_users' => $this->users->all()]; }
}