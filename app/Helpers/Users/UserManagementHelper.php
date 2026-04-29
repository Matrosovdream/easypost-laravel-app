<?php

namespace App\Helpers\Users;

use Illuminate\Support\Collection;

class UserManagementHelper
{
    public function toListItem(object $r): array
    {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'email' => $r->email,
            'role_slug' => $r->role_slug,
            'role_name' => $r->role_name,
            'is_active' => (bool) $r->is_active,
            'last_login_at' => $r->last_login_at,
            'spending_cap_cents' => $r->spending_cap_cents,
            'daily_cap_cents' => $r->daily_cap_cents,
            'client_id' => $r->client_id,
            'membership_status' => $r->membership_status,
        ];
    }

    public function toListPayload(Collection $rows): array
    {
        return [
            'data' => $rows->map(fn ($r) => $this->toListItem($r))->values(),
        ];
    }

    public function toManagerListItem(object $r): array
    {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'email' => $r->email,
            'is_active' => (bool) $r->is_active,
            'membership_status' => $r->membership_status,
            'last_login_at' => $r->last_login_at,
            'joined_at' => $r->joined_at,
            'created_at' => $r->created_at,
            'shipments_assigned' => (int) $r->shipments_assigned,
            'shipments_approved' => (int) $r->shipments_approved,
            'shipments_approved_30d' => (int) $r->shipments_approved_30d,
            'approvals_pending' => (int) $r->approvals_pending,
        ];
    }

    public function toManagerListPayload(Collection $rows): array
    {
        return [
            'data' => $rows->map(fn ($r) => $this->toManagerListItem($r))->values(),
        ];
    }

    private function baseFields(object $r): array
    {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'email' => $r->email,
            'is_active' => (bool) $r->is_active,
            'membership_status' => $r->membership_status,
            'last_login_at' => $r->last_login_at,
            'joined_at' => $r->joined_at,
            'created_at' => $r->created_at,
        ];
    }

    public function toPeopleListPayload(Collection $rows, string $roleSlug): array
    {
        $columns = $this->columnsForRole($roleSlug);
        $statKeys = array_column($columns, 'key');

        $data = $rows->map(function ($r) use ($statKeys) {
            $row = $this->baseFields($r);
            foreach ($statKeys as $k) {
                $row[$k] = isset($r->$k) ? (int) $r->$k : 0;
            }
            return $row;
        })->values();

        return [
            'role' => $roleSlug,
            'columns' => $columns,
            'data' => $data,
        ];
    }

    /**
     * @return array<int, array{key:string, label:string, severity_when_gt0?:string}>
     */
    private function columnsForRole(string $roleSlug): array
    {
        return match ($roleSlug) {
            'manager' => [
                ['key' => 'shipments_assigned',     'label' => 'Assigned'],
                ['key' => 'shipments_approved_30d', 'label' => 'Approved (30d)'],
                ['key' => 'shipments_approved_total','label' => 'Approved (all)'],
                ['key' => 'approvals_pending',      'label' => 'Pending', 'severity_when_gt0' => 'warn'],
            ],
            'shipper' => [
                ['key' => 'shipments_assigned_open','label' => 'Open queue'],
                ['key' => 'shipments_packed_30d',   'label' => 'Packed (30d)'],
            ],
            'cs_agent' => [
                ['key' => 'returns_handled_30d',    'label' => 'Returns (30d)'],
                ['key' => 'claims_assigned',        'label' => 'Claims'],
                ['key' => 'claims_open',            'label' => 'Claims open', 'severity_when_gt0' => 'warn'],
            ],
            'client' => [
                ['key' => 'shipments_30d', 'label' => 'Shipments (30d)'],
                ['key' => 'returns_open',  'label' => 'Returns open', 'severity_when_gt0' => 'warn'],
            ],
            default => [],
        };
    }
}
