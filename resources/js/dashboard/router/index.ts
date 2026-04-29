import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@dashboard/stores/auth';

const stub = (title: string) => ({
    template: `<div class="bg-white rounded-xl border border-surface-200 p-6"><h1 class="text-2xl font-bold text-surface-900">${title}</h1><p class="mt-2 text-surface-600">This page lands in a later step.</p></div>`,
});

const routes: RouteRecordRaw[] = [
    {
        path: '/dashboard',
        component: () => import('@dashboard/pages/Home.vue'),
    },
    { path: '/dashboard/shipments', component: () => import('@dashboard/pages/Shipments/Index.vue') },
    { path: '/dashboard/shipments/create', component: () => import('@dashboard/pages/Shipments/Create.vue') },
    { path: '/dashboard/shipments/new', redirect: '/dashboard/shipments/create' },
    { path: '/dashboard/shipments/approvals', component: () => import('@dashboard/pages/Shipments/Approvals.vue') },
    { path: '/dashboard/shipments/exceptions', component: () => import('@dashboard/pages/Shipments/Exceptions.vue') },
    { path: '/dashboard/shipments/:id', component: () => import('@dashboard/pages/Shipments/Show.vue') },
    { path: '/dashboard/my-queue', component: () => import('@dashboard/pages/Shipments/MyQueue.vue') },
    { path: '/dashboard/approvals', redirect: '/dashboard/shipments/approvals' },
    { path: '/dashboard/exceptions', redirect: '/dashboard/shipments/exceptions' },
    { path: '/dashboard/print', component: stub('Print queue') },
    { path: '/dashboard/batches', component: () => import('@dashboard/pages/Batches/Index.vue') },
    { path: '/dashboard/batches/create', component: () => import('@dashboard/pages/Batches/Create.vue') },
    { path: '/dashboard/batches/:id', component: () => import('@dashboard/pages/Batches/Show.vue') },
    { path: '/dashboard/scan-forms', component: () => import('@dashboard/pages/ScanForms/Index.vue') },
    { path: '/dashboard/scan-forms/create', component: () => import('@dashboard/pages/ScanForms/Create.vue') },
    { path: '/dashboard/scanforms', redirect: '/dashboard/scan-forms' },
    { path: '/dashboard/pickups', component: () => import('@dashboard/pages/Pickups/Index.vue') },
    { path: '/dashboard/pickups/schedule', component: () => import('@dashboard/pages/Pickups/Schedule.vue') },
    { path: '/dashboard/pickups/:id', component: () => import('@dashboard/pages/Pickups/Show.vue') },
    { path: '/dashboard/returns', component: () => import('@dashboard/pages/Returns/Index.vue') },
    { path: '/dashboard/returns/create', component: () => import('@dashboard/pages/Returns/Create.vue') },
    { path: '/dashboard/returns/:id', component: () => import('@dashboard/pages/Returns/Show.vue') },
    { path: '/dashboard/claims', component: () => import('@dashboard/pages/Claims/Index.vue') },
    { path: '/dashboard/claims/create', component: () => import('@dashboard/pages/Claims/Create.vue') },
    { path: '/dashboard/claims/:id', component: () => import('@dashboard/pages/Claims/Show.vue') },
    { path: '/dashboard/insurance', component: () => import('@dashboard/pages/Insurance/Index.vue') },
    { path: '/dashboard/addresses', component: () => import('@dashboard/pages/Addresses/Index.vue') },
    { path: '/dashboard/addresses/create', component: () => import('@dashboard/pages/Addresses/Create.vue') },
    { path: '/dashboard/addresses/:id', component: () => import('@dashboard/pages/Addresses/Show.vue') },
    { path: '/dashboard/trackers', component: () => import('@dashboard/pages/Trackers/Index.vue') },
    { path: '/dashboard/trackers/create', component: () => import('@dashboard/pages/Trackers/Create.vue') },
    { path: '/dashboard/trackers/:id', component: () => import('@dashboard/pages/Trackers/Show.vue') },
    { path: '/dashboard/reports', component: () => import('@dashboard/pages/Reports/Index.vue') },
    { path: '/dashboard/analytics', redirect: '/dashboard/analytics/overview' },
    { path: '/dashboard/analytics/overview', component: () => import('@dashboard/pages/Analytics/Overview.vue') },
    { path: '/dashboard/analytics/carriers', component: () => import('@dashboard/pages/Analytics/Carriers.vue') },
    { path: '/dashboard/ops/print-queue', component: () => import('@dashboard/pages/Ops/PrintQueue.vue') },
    { path: '/dashboard/print', redirect: '/dashboard/ops/print-queue' },
    { path: '/dashboard/clients', component: () => import('@dashboard/pages/Clients/Index.vue') },
    { path: '/dashboard/clients/create', component: () => import('@dashboard/pages/Clients/Create.vue') },
    { path: '/dashboard/clients/:id', component: () => import('@dashboard/pages/Clients/Show.vue') },
    { path: '/dashboard/settings', redirect: '/dashboard/settings/team' },
    { path: '/dashboard/settings/team', component: () => import('@dashboard/pages/Settings/Team.vue') },
    { path: '/dashboard/settings/users', component: () => import('@dashboard/pages/Settings/Users.vue') },
    { path: '/dashboard/settings/managers', redirect: '/dashboard/admin/people/manager' },
    { path: '/dashboard/admin/overview', component: () => import('@dashboard/pages/Admin/Overview.vue') },
    { path: '/dashboard/admin/people/:role', component: () => import('@dashboard/pages/Settings/People.vue') },
    { path: '/dashboard/settings/invitations', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Invitations', description: 'Pending invites and accept-link tracking.' } },
    { path: '/dashboard/settings/policies', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Approval policies', description: 'Spending caps, approval thresholds, auto-rules.' } },
    { path: '/dashboard/notifications/outbox', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Notifications outbox', description: 'Customer-facing comms sent on this team.' } },
    { path: '/dashboard/settings/audit-log', component: () => import('@dashboard/pages/Settings/AuditLog.vue') },
    { path: '/dashboard/settings/carriers', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Carrier accounts', description: 'Connect UPS, FedEx, DHL and more.' } },
    { path: '/dashboard/settings/warehouses', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Warehouses', description: 'Manage warehouses and stations.' } },
    { path: '/dashboard/settings/approvals', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Approval rules', description: 'Configure approval thresholds per role.' } },
    { path: '/dashboard/settings/branding', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Branding', description: 'Logo, colors, tracking page appearance.' } },
    { path: '/dashboard/settings/webhooks', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Webhooks', description: 'Outgoing webhooks to your systems.' } },
    { path: '/dashboard/settings/notifications', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Notifications', description: 'Team-wide notification templates.' } },
    { path: '/dashboard/settings/api-keys', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'API keys', description: 'Machine-to-machine credentials.' } },
    { path: '/dashboard/settings/integrations', component: () => import('@dashboard/pages/Settings/Stub.vue'), props: { title: 'Integrations', description: 'Slack, Zapier, etc.' } },
    { path: '/dashboard/settings/billing', component: () => import('@dashboard/pages/Settings/Billing.vue') },
    { path: '/dashboard/profile', component: () => import('@dashboard/pages/Profile/Profile.vue') },
    { path: '/dashboard/profile/pin', component: () => import('@dashboard/pages/Profile/Pin.vue') },
    { path: '/dashboard/profile/security', component: () => import('@dashboard/pages/Profile/Security.vue') },
    { path: '/dashboard/profile/notifications', component: () => import('@dashboard/pages/Profile/Notifications.vue') },
    {
        path: '/dashboard/403',
        name: 'dashboard.forbidden',
        component: () => import('@dashboard/pages/Forbidden.vue'),
    },
    {
        path: '/dashboard/locked',
        name: 'dashboard.locked',
        component: () => import('@dashboard/pages/Locked.vue'),
    },
    {
        path: '/dashboard/:pathMatch(.*)*',
        name: 'dashboard.notfound',
        component: () => import('@dashboard/pages/NotFound.vue'),
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() { return { top: 0 }; },
});

router.beforeEach(async (to, _from, next) => {
    const auth = useAuthStore();
    if (!auth.loaded) await auth.fetchMe();

    if (!auth.isAuthenticated) {
        window.location.href = '/portal/login';
        return;
    }

    if (auth.teamStatus === 'locked' && to.name !== 'dashboard.locked') {
        next({ name: 'dashboard.locked' });
        return;
    }

    next();
});
