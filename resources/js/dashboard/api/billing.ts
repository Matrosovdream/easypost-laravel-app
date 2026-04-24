import client from '@shared/api/client';

export type PlanResp = {
    plan: string;
    status: string;
    mode: string;
    trial_ends_at: string | null;
    stripe_customer_id: string | null;
    usage: {
        used: number;
        cap: number | null;
        remaining: number | null;
        reset_at: string;
    };
    available_plans: string[];
};

export const billingApi = {
    async plan(): Promise<PlanResp> {
        const { data } = await client.get('/billing/plan');
        return data;
    },
    async checkout(plan: string): Promise<{ url: string; simulated?: boolean }> {
        const { data } = await client.post('/billing/checkout', { plan });
        return data;
    },
    async portal(): Promise<{ url: string; simulated?: boolean }> {
        const { data } = await client.post('/billing/portal');
        return data;
    },
};
