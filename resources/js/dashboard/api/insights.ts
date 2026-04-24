import client from '@shared/api/client';

export type OverviewResp = {
    total_shipments: number;
    total_cost_cents: number;
    by_status: Array<{ status: string; count: number; cost_cents: number }>;
    by_carrier: Array<{ carrier: string; count: number; cost_cents: number }>;
    daily_30d: Array<{ date: string; count: number; cost_cents: number }>;
};

export type CarriersResp = {
    carriers: Array<{
        carrier: string;
        total: number;
        delivered: number;
        voided: number;
        delivery_rate_pct: number;
        cost_cents: number;
        avg_cost_cents: number;
    }>;
};

export type ReportItem = {
    id: number;
    type: string;
    status: string;
    start_date: string;
    end_date: string;
    s3_key: string | null;
    created_at: string;
};

export type PrintQueueItem = {
    id: number;
    reference: string | null;
    tracking_code: string | null;
    carrier: string | null;
    service: string | null;
    label_url: string | null;
    assigned_to: number | null;
    to_address: { name: string | null; city: string | null; state: string | null; country: string | null } | null;
};

export const insightsApi = {
    async overview(): Promise<OverviewResp> {
        const { data } = await client.get('/analytics/overview');
        return data;
    },
    async carriers(): Promise<CarriersResp> {
        const { data } = await client.get('/analytics/carriers');
        return data;
    },
    async reports(): Promise<{ data: ReportItem[] }> {
        const { data } = await client.get('/reports');
        return data;
    },
    async createReport(input: { type: string; start_date: string; end_date: string }): Promise<{ id: number; status: string }> {
        const { data } = await client.post('/reports', input);
        return data;
    },
    async printQueue(): Promise<{ data: PrintQueueItem[] }> {
        const { data } = await client.get('/ops/print-queue');
        return data;
    },
};
