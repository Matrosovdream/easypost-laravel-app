import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { marketingRoutes } from './routes.marketing';
import { portalRoutes } from './routes.portal';
import { trackingRoutes } from './routes.tracking';

const catchAll: RouteRecordRaw = {
    path: '/:pathMatch(.*)*',
    component: () => import('@web/pages/Public/NotFound.vue'),
    meta: { layout: 'marketing' },
};

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        ...marketingRoutes,
        ...portalRoutes,
        ...trackingRoutes,
        catchAll,
    ],
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        if (to.hash) return { el: to.hash, behavior: 'smooth' };
        return { top: 0 };
    },
});
