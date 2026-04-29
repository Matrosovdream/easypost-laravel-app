export type NavItem = {
    label: string;
    icon?: string;
    to?: string;
    badge?: 'approvalsCount' | 'exceptionsCount' | 'returnsCount' | 'claimsCount' | 'printReady' | 'queueCount';
    right?: string;
    anyRight?: string[];
    roles?: string[];
    hideFromRoles?: string[];
    items?: NavItem[];
};

export type NavSection = {
    label: string;
    items: NavItem[];
    roles?: string[];
    hideFromRoles?: string[];
};

export const nav: NavSection[] = [
    // ========== ADMIN ==========
    {
        label: 'Overview',
        roles: ['admin'],
        items: [
            { label: 'Tenant overview', icon: 'pi pi-th-large',  to: '/dashboard/admin/overview' },
            { label: 'Audit log',       icon: 'pi pi-history',   to: '/dashboard/settings/audit-log' },
        ],
    },
    {
        label: 'People',
        roles: ['admin'],
        items: [
            { label: 'Managers',      icon: 'pi pi-id-card',  to: '/dashboard/admin/people/manager' },
            { label: 'Shippers',      icon: 'pi pi-box',      to: '/dashboard/admin/people/shipper' },
            { label: 'CS Agents',     icon: 'pi pi-shield',   to: '/dashboard/admin/people/cs_agent' },
            { label: 'Clients',      icon: 'pi pi-building', to: '/dashboard/admin/people/client' },
            { label: 'Viewers',      icon: 'pi pi-eye',      to: '/dashboard/admin/people/viewer' },
            { label: 'Users & roles', icon: 'pi pi-users',    to: '/dashboard/settings/users' },
            { label: 'Invitations',   icon: 'pi pi-send',     to: '/dashboard/settings/invitations' },
        ],
    },
    {
        label: 'Data',
        roles: ['admin'],
        items: [
            { label: 'Addresses', icon: 'pi pi-map-marker', to: '/dashboard/addresses' },
            { label: 'Trackers',  icon: 'pi pi-compass',    to: '/dashboard/trackers' },
            { label: 'Reports',   icon: 'pi pi-chart-line', to: '/dashboard/reports' },
            { label: 'Analytics', icon: 'pi pi-chart-bar',  to: '/dashboard/analytics' },
        ],
    },
    {
        label: 'Configuration',
        roles: ['admin'],
        items: [
            { label: 'Tenant settings',     icon: 'pi pi-cog',          to: '/dashboard/settings/team' },
            { label: 'Carrier accounts',    icon: 'pi pi-truck',        to: '/dashboard/settings/carriers' },
            { label: 'Webhooks',            icon: 'pi pi-link',         to: '/dashboard/settings/webhooks' },
            { label: 'API keys',            icon: 'pi pi-key',          to: '/dashboard/settings/api-keys' },
            { label: 'Approval policies',   icon: 'pi pi-shield',       to: '/dashboard/settings/policies' },
            { label: 'Notifications',       icon: 'pi pi-bell',         to: '/dashboard/settings/notifications' },
            { label: 'Branding',            icon: 'pi pi-palette',      to: '/dashboard/settings/branding' },
            { label: 'Billing',             icon: 'pi pi-credit-card',  to: '/dashboard/settings/billing' },
        ],
    },
    // ========== MANAGER ==========
    {
        label: 'Home',
        roles: ['manager'],
        items: [
            { label: 'Home', icon: 'pi pi-home', to: '/dashboard' },
        ],
    },
    {
        label: 'Operations',
        roles: ['manager'],
        items: [
            { label: 'Shipments',  icon: 'pi pi-box',                  to: '/dashboard/shipments' },
            { label: 'Approvals',  icon: 'pi pi-check-square',         to: '/dashboard/shipments/approvals',  right: 'shipments.approve', badge: 'approvalsCount' },
            { label: 'Exceptions', icon: 'pi pi-exclamation-triangle', to: '/dashboard/shipments/exceptions', badge: 'exceptionsCount' },
            { label: 'Print queue',icon: 'pi pi-print',                to: '/dashboard/ops/print-queue',      right: 'labels.print', badge: 'printReady' },
            { label: 'Batches',    icon: 'pi pi-clone',                to: '/dashboard/batches',              right: 'batches.manage' },
            { label: 'Scan forms', icon: 'pi pi-file',                 to: '/dashboard/scan-forms',           right: 'scan_forms.manage' },
            { label: 'Pickups',    icon: 'pi pi-calendar',             to: '/dashboard/pickups',              right: 'pickups.manage' },
        ],
    },
    {
        label: 'Customer service',
        roles: ['manager'],
        items: [
            { label: 'Returns',   icon: 'pi pi-reply',   to: '/dashboard/returns',   badge: 'returnsCount' },
            { label: 'Claims',    icon: 'pi pi-shield',  to: '/dashboard/claims',    right: 'claims.view', badge: 'claimsCount' },
            { label: 'Insurance', icon: 'pi pi-umbrella',to: '/dashboard/insurance' },
        ],
    },
    {
        label: 'Data',
        roles: ['manager'],
        items: [
            { label: 'Addresses', icon: 'pi pi-map-marker', to: '/dashboard/addresses' },
            { label: 'Trackers',  icon: 'pi pi-compass',    to: '/dashboard/trackers' },
            { label: 'Reports',   icon: 'pi pi-chart-line', to: '/dashboard/reports' },
            { label: 'Analytics', icon: 'pi pi-chart-bar',  to: '/dashboard/analytics' },
        ],
    },
    // ========== SHIPPER ==========
    {
        label: 'Home',
        roles: ['shipper'],
        items: [
            { label: 'Home', icon: 'pi pi-home', to: '/dashboard' },
        ],
    },
    {
        label: 'My work',
        roles: ['shipper'],
        items: [
            { label: 'My queue',   icon: 'pi pi-list',     to: '/dashboard/my-queue',        badge: 'queueCount' },
            { label: 'Print queue',icon: 'pi pi-print',    to: '/dashboard/ops/print-queue', badge: 'printReady' },
            { label: 'Batches',    icon: 'pi pi-clone',    to: '/dashboard/batches' },
            { label: 'Scan forms', icon: 'pi pi-file',     to: '/dashboard/scan-forms' },
            { label: 'Pickups',    icon: 'pi pi-calendar', to: '/dashboard/pickups' },
        ],
    },
    {
        label: 'Reference',
        roles: ['shipper'],
        items: [
            { label: 'Shipments',  icon: 'pi pi-box',                  to: '/dashboard/shipments' },
            { label: 'Exceptions', icon: 'pi pi-exclamation-triangle', to: '/dashboard/shipments/exceptions', badge: 'exceptionsCount' },
            { label: 'Addresses',  icon: 'pi pi-map-marker',           to: '/dashboard/addresses' },
            { label: 'Trackers',   icon: 'pi pi-compass',              to: '/dashboard/trackers' },
        ],
    },

    // ========== CS_AGENT ==========
    {
        label: 'Home',
        roles: ['cs_agent'],
        items: [
            { label: 'Home', icon: 'pi pi-home', to: '/dashboard' },
        ],
    },
    {
        label: 'Customer service',
        roles: ['cs_agent'],
        items: [
            { label: 'Returns',              icon: 'pi pi-reply',    to: '/dashboard/returns',               badge: 'returnsCount' },
            { label: 'Claims',               icon: 'pi pi-shield',   to: '/dashboard/claims',                badge: 'claimsCount' },
            { label: 'Insurance',            icon: 'pi pi-umbrella', to: '/dashboard/insurance' },
            { label: 'Notifications outbox', icon: 'pi pi-send',     to: '/dashboard/notifications/outbox' },
        ],
    },

    // ========== CLIENT ==========
    {
        label: 'My account',
        roles: ['client'],
        items: [
            { label: 'Dashboard', icon: 'pi pi-home', to: '/dashboard' },
        ],
    },
    {
        label: 'My shipments',
        roles: ['client'],
        items: [
            { label: 'Create shipment', icon: 'pi pi-plus',                 to: '/dashboard/shipments/create' },
            { label: 'My shipments',    icon: 'pi pi-box',                  to: '/dashboard/shipments' },
            { label: 'Tracking',        icon: 'pi pi-exclamation-triangle', to: '/dashboard/shipments/exceptions', badge: 'exceptionsCount' },
        ],
    },
    {
        label: 'My orders',
        roles: ['client'],
        items: [
            { label: 'Returns',   icon: 'pi pi-reply',      to: '/dashboard/returns', badge: 'returnsCount' },
            { label: 'Addresses', icon: 'pi pi-map-marker', to: '/dashboard/addresses' },
        ],
    },
    {
        label: 'My reports',
        roles: ['client'],
        items: [
            { label: 'Reports', icon: 'pi pi-chart-line', to: '/dashboard/reports' },
        ],
    },

    // ========== VIEWER ==========
    {
        label: 'Insights',
        roles: ['viewer'],
        items: [
            { label: 'Tenant overview', icon: 'pi pi-th-large',     to: '/dashboard/admin/overview' },
            { label: 'Reports',         icon: 'pi pi-chart-line',   to: '/dashboard/reports' },
            { label: 'Analytics',       icon: 'pi pi-chart-bar',    to: '/dashboard/analytics' },
            { label: 'Billing',         icon: 'pi pi-credit-card',  to: '/dashboard/settings/billing' },
        ],
    },
    {
        label: 'Browse',
        roles: ['viewer'],
        items: [
            { label: 'Shipments', icon: 'pi pi-box',     to: '/dashboard/shipments' },
            { label: 'Returns',   icon: 'pi pi-reply',   to: '/dashboard/returns' },
            { label: 'Claims',    icon: 'pi pi-shield',  to: '/dashboard/claims' },
            { label: 'Trackers',  icon: 'pi pi-compass', to: '/dashboard/trackers' },
            { label: 'Audit log', icon: 'pi pi-history', to: '/dashboard/settings/audit-log' },
        ],
    },
];
