import client from '@shared/api/client';

export type ClientItem = {
    id: number;
    company_name: string;
    contact_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    flexrate_markup_pct: number;
    per_service_markups: Record<string, number> | null;
    billing_mode: string;
    credit_terms_days: number;
    status: string;
    ep_endshipper_id: string | null;
    notes: string | null;
    created_at: string;
};

export type InvoiceLine = {
    shipment_id: number;
    reference: string | null;
    carrier: string;
    service: string;
    tracking_code: string | null;
    carrier_cost_cents: number;
    markup_pct: number;
    markup_cents: number;
    charge_cents: number;
    created_at: string;
};

export type InvoiceResp = {
    client_id: number;
    company_name: string;
    period: { from: string; to: string };
    lines: InvoiceLine[];
    totals: { count: number; carrier_cost_cents: number; markup_cents: number; charge_cents: number };
};

export const clientsApi = {
    async list(): Promise<{ data: ClientItem[] }> {
        const { data } = await client.get('/clients');
        return data;
    },
    async show(id: number): Promise<ClientItem> {
        const { data } = await client.get(`/clients/${id}`);
        return data;
    },
    async create(input: Partial<ClientItem> & { company_name: string }): Promise<ClientItem> {
        const { data } = await client.post('/clients', input);
        return data;
    },
    async update(id: number, input: Partial<ClientItem>): Promise<ClientItem> {
        const { data } = await client.put(`/clients/${id}`, input);
        return data;
    },
    async setFlexRate(id: number, markupPct: number, perService?: Record<string, number>): Promise<ClientItem> {
        const { data } = await client.post(`/clients/${id}/flex-rate`, {
            flexrate_markup_pct: markupPct,
            per_service_markups: perService,
        });
        return data;
    },
    async invoice(id: number, from: string, to: string): Promise<InvoiceResp> {
        const { data } = await client.post(`/clients/${id}/invoice`, { from, to });
        return data;
    },
};
