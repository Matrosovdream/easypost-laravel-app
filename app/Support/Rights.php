<?php

namespace App\Support;

/**
 * Authoritative catalog of every permission slug the app recognizes.
 *
 * `role_rights.right` values MUST exist in CATALOG.
 * `Rights::byRole()` is the seed data source for `RoleRightsSeeder`.
 *
 * See project-plan/roles.md §9 and project-plan/migrations-plan.md §11.
 */
final class Rights
{
    public const CATALOG = [
        // Dashboard
        'dashboard.view',

        // Shipments
        'shipments.create',
        'shipments.view.any',
        'shipments.view.own',
        'shipments.view.assigned',
        'shipments.buy',
        'shipments.buy.over_cap',
        'shipments.approve',
        'shipments.assign',
        'shipments.void',

        // Addresses
        'addresses.create',
        'addresses.verify',
        'addresses.override_verification',

        // Labels
        'labels.print',
        'labels.reprint',
        'labels.convert',

        // Batches / Scan forms / Pickups
        'batches.manage',
        'scan_forms.manage',
        'pickups.manage',

        // Trackers
        'trackers.view.any',
        'trackers.view.own',
        'trackers.create.standalone',
        'trackers.delete',

        // Insurance / Claims
        'insurance.add',
        'insurance.add.high_value',
        'claims.view',
        'claims.open',
        'claims.approve',

        // Returns
        'returns.view.any',
        'returns.view.own',
        'returns.request.any',
        'returns.request.own',
        'returns.approve',
        'returns.refund',

        // Reports
        'reports.view',
        'reports.create',
        'reports.create.own',
        'reports.download',
        'reports.schedule',

        // Carriers / Webhooks / API keys
        'carrier_accounts.manage',
        'webhooks.manage',
        'api_keys.manage',

        // Users / Team management
        'users.invite',
        'users.remove',
        'users.manage',
        'users.role.assign',
        'users.role.assign.admin',

        // Billing
        'billing.view',
        'billing.manage',

        // Audit
        'audit_log.view.any',
        'audit_log.view.own',

        // Clients (3PL)
        'clients.view',
        'clients.manage',
        'client_portal.view.own',
        'client_pnl.view',

        // Analytics
        'analytics.view',

        // Settings
        'settings.team.edit',
        'settings.notifications.edit',

        // Ops (warehouse print queue)
        'print.queue.manage',

        // Customer comms
        'notifications.send',
    ];

    /**
     * @return array<int, array{right:string, group:string, label:string}>
     */
    public static function catalog(): array
    {
        return array_map(
            fn (string $r): array => [
                'right' => $r,
                'group' => explode('.', $r)[0],
                'label' => ucwords(str_replace(['.', '_'], [' → ', ' '], $r)),
            ],
            self::CATALOG,
        );
    }

    /**
     * Role → rights mapping. Authoritative seed for RoleRightsSeeder.
     *
     * @return array<string, array<int, string>>
     */
    public static function byRole(): array
    {
        $admin = self::CATALOG;

        $manager = array_values(array_diff($admin, [
            'billing.manage',
            'carrier_accounts.manage',
            'webhooks.manage',
            'api_keys.manage',
            'users.role.assign.admin',
        ]));

        $shipper = [
            'dashboard.view',
            'shipments.create',
            'shipments.view.assigned',
            'shipments.buy',
            'addresses.create',
            'addresses.verify',
            'labels.print',
            'labels.reprint',
            'labels.convert',
            'batches.manage',
            'scan_forms.manage',
            'pickups.manage',
            'trackers.view.any',
            'print.queue.manage',
        ];

        $csAgent = [
            'dashboard.view',
            'shipments.create',
            'shipments.view.any',
            'shipments.buy',
            'shipments.void',
            'addresses.create',
            'addresses.verify',
            'trackers.view.any',
            'trackers.create.standalone',
            'insurance.add',
            'claims.view',
            'claims.open',
            'returns.view.any',
            'returns.request.any',
            'returns.approve',
            'returns.refund',
            'notifications.send',
        ];

        $client = [
            'dashboard.view',
            'client_portal.view.own',
            'shipments.create',
            'shipments.view.own',
            'addresses.create',
            'addresses.verify',
            'trackers.view.own',
            'returns.view.own',
            'returns.request.own',
            'reports.create.own',
            'reports.download',
        ];

        $viewer = [
            'dashboard.view',
            'shipments.view.any',
            'trackers.view.any',
            'returns.view.any',
            'claims.view',
            'reports.view',
            'reports.download',
            'audit_log.view.any',
            'analytics.view',
            'billing.view',
            'client_pnl.view',
        ];

        return [
            'admin'    => $admin,
            'manager'  => $manager,
            'shipper'  => $shipper,
            'cs_agent' => $csAgent,
            'client'   => $client,
            'viewer'   => $viewer,
        ];
    }

    public static function exists(string $right): bool
    {
        return in_array($right, self::CATALOG, true);
    }
}
