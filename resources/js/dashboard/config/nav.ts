export type NavItem = {
    label: string;
    icon?: string;
    to?: string;
    badge?: 'approvalsCount' | 'exceptionsCount' | 'returnsCount' | 'claimsCount' | 'printReady' | 'queueCount';
    right?: string;
    anyRight?: string[];
    roles?: string[];
    items?: NavItem[];
};

export type NavSection = {
    label: string;
    items: NavItem[];
};

export const nav: NavSection[] = [
    {
        label: 'Home',
        items: [
            { label: 'Home', icon: 'pi pi-home', to: '/dashboard' },
        ],
    },
    {
        label: 'Operations',
        items: [
            {
                label: 'Shipments',
                icon: 'pi pi-box',
                to: '/dashboard/shipments',
                anyRight: ['shipments.view.any', 'shipments.view.assigned', 'shipments.view.own'],
            },
            {
                label: 'My queue',
                icon: 'pi pi-list',
                to: '/dashboard/my-queue',
                right: 'shipments.view.assigned',
            },
            {
                label: 'Approvals',
                icon: 'pi pi-check-square',
                to: '/dashboard/shipments/approvals',
                right: 'shipments.approve',
                badge: 'approvalsCount',
            },
            {
                label: 'Exceptions',
                icon: 'pi pi-exclamation-triangle',
                to: '/dashboard/shipments/exceptions',
                anyRight: ['trackers.view.any', 'trackers.view.own'],
                badge: 'exceptionsCount',
            },
            {
                label: 'Print queue',
                icon: 'pi pi-print',
                to: '/dashboard/ops/print-queue',
                right: 'labels.print',
                badge: 'printReady',
            },
            {
                label: 'Batches',
                icon: 'pi pi-clone',
                to: '/dashboard/batches',
                right: 'batches.manage',
            },
            {
                label: 'Scan forms',
                icon: 'pi pi-file',
                to: '/dashboard/scan-forms',
                right: 'scan_forms.manage',
            },
            {
                label: 'Pickups',
                icon: 'pi pi-calendar',
                to: '/dashboard/pickups',
                right: 'pickups.manage',
            },
        ],
    },
    {
        label: 'Customer service',
        items: [
            {
                label: 'Returns',
                icon: 'pi pi-reply',
                to: '/dashboard/returns',
                anyRight: ['returns.view.any', 'returns.view.own', 'returns.approve', 'returns.request.any', 'returns.request.own'],
                badge: 'returnsCount',
            },
            {
                label: 'Claims',
                icon: 'pi pi-shield',
                to: '/dashboard/claims',
                right: 'claims.view',
                badge: 'claimsCount',
            },
            {
                label: 'Insurance',
                icon: 'pi pi-umbrella',
                to: '/dashboard/insurance',
                anyRight: ['insurance.add', 'insurance.add.high_value'],
            },
        ],
    },
    {
        label: 'Data',
        items: [
            {
                label: 'Addresses',
                icon: 'pi pi-map-marker',
                to: '/dashboard/addresses',
                anyRight: ['addresses.create', 'addresses.verify', 'shipments.view.any', 'shipments.view.assigned', 'shipments.view.own'],
            },
            {
                label: 'Trackers',
                icon: 'pi pi-compass',
                to: '/dashboard/trackers',
                anyRight: ['trackers.view.any', 'trackers.view.own'],
            },
            {
                label: 'Reports',
                icon: 'pi pi-chart-line',
                to: '/dashboard/reports',
                right: 'reports.view',
            },
            {
                label: 'Analytics',
                icon: 'pi pi-chart-bar',
                to: '/dashboard/analytics',
                right: 'analytics.view',
            },
        ],
    },
    {
        label: 'Business',
        items: [
            {
                label: 'Clients',
                icon: 'pi pi-building',
                to: '/dashboard/clients',
                anyRight: ['clients.view', 'clients.manage'],
            },
        ],
    },
    {
        label: 'Settings',
        items: [
            {
                label: 'Team',
                icon: 'pi pi-cog',
                to: '/dashboard/settings/team',
                anyRight: ['settings.team.edit', 'users.manage'],
            },
            {
                label: 'Users & roles',
                icon: 'pi pi-users',
                to: '/dashboard/settings/users',
                right: 'users.manage',
            },
            {
                label: 'Carrier accounts',
                icon: 'pi pi-truck',
                to: '/dashboard/settings/carriers',
                right: 'carrier_accounts.manage',
            },
            {
                label: 'Webhooks',
                icon: 'pi pi-link',
                to: '/dashboard/settings/webhooks',
                right: 'webhooks.manage',
            },
            {
                label: 'Audit log',
                icon: 'pi pi-history',
                to: '/dashboard/settings/audit-log',
                anyRight: ['audit_log.view.any', 'audit_log.view.own'],
            },
            {
                label: 'Billing',
                icon: 'pi pi-credit-card',
                to: '/dashboard/settings/billing',
                right: 'billing.manage',
            },
            {
                label: 'Profile',
                icon: 'pi pi-user',
                to: '/dashboard/profile',
            },
        ],
    },
];
