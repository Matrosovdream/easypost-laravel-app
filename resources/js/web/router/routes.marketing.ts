import type { RouteRecordRaw } from 'vue-router';

export const marketingRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        component: () => import('@web/pages/Public/Home.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/pricing',
        component: () => import('@web/pages/Public/Pricing.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/features',
        component: () => import('@web/pages/Public/Features.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/customers',
        component: () => import('@web/pages/Public/Customers.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/about',
        component: () => import('@web/pages/Public/About.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/contact',
        component: () => import('@web/pages/Public/Contact.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/blog',
        component: () => import('@web/pages/Public/Blog/Index.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/blog/:slug',
        component: () => import('@web/pages/Public/Blog/Show.vue'),
        meta: { layout: 'marketing' },
    },
];
