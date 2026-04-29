import type { RouteRecordRaw } from 'vue-router';

export const trackingRoutes: RouteRecordRaw[] = [
    {
        path: '/track/:code?',
        component: () => import('@web/pages/Public/Tracking.vue'),
        meta: { layout: 'tracking' },
    },
];
