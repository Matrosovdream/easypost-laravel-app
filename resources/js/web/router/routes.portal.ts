import type { RouteRecordRaw } from 'vue-router';

export const portalRoutes: RouteRecordRaw[] = [
    {
        path: '/portal/login',
        component: () => import('@web/pages/Portal/Login.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/register',
        component: () => import('@web/pages/Portal/Register.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/accept-invite/:token',
        component: () => import('@web/pages/Portal/AcceptInvite.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/forgot-password',
        component: () => import('@web/pages/Portal/ForgotPassword.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/reset-password/:token',
        component: () => import('@web/pages/Portal/ResetPassword.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/verify-email',
        component: () => import('@web/pages/Portal/VerifyEmail.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/two-factor',
        component: () => import('@web/pages/Portal/TwoFactor.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/oauth/:provider/callback',
        component: () => import('@web/pages/Portal/OauthCallback.vue'),
        meta: { layout: 'portal' },
    },
    {
        path: '/portal/terms',
        component: () => import('@web/pages/Public/Legal/Terms.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/portal/privacy',
        component: () => import('@web/pages/Public/Legal/Privacy.vue'),
        meta: { layout: 'marketing' },
    },
    {
        path: '/portal/dpa',
        component: () => import('@web/pages/Public/Legal/Dpa.vue'),
        meta: { layout: 'marketing' },
    },
];
