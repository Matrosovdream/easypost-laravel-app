import client from '@shared/api/client';

type Paginated<T> = { data: T[]; meta: { current_page: number; last_page: number; total: number } };

export type ReturnItem = {
    id: number;
    status: string;
    reason: string | null;
    original_shipment_id: number;
    return_shipment_id: number | null;
    auto_refund: boolean;
    created_by: { id: number; name: string } | null;
    created_at: string;
};

export type ReturnDetail = ReturnItem & {
    items: string[] | null;
    notes: string | null;
    approved_by: { id: number; name: string } | null;
    approved_at: string | null;
    original_shipment: { id: number; reference: string | null; tracking_code: string | null } | null;
    return_shipment: { id: number; reference: string | null; status: string; tracking_code: string | null } | null;
};

export type ClaimItem = {
    id: number;
    state: string;
    type: string;
    amount_cents: number;
    recovered_cents: number | null;
    shipment_id: number;
    shipment: { id: number; reference: string | null; tracking_code: string | null } | null;
    assignee: { id: number; name: string } | null;
    approver: { id: number; name: string } | null;
    paid_at: string | null;
    closed_at: string | null;
    created_at: string;
};

export type ClaimDetail = ClaimItem & {
    description: string;
    timeline: Array<{ at: string; event: string; by: number; [key: string]: unknown }> | null;
    ep_claim_id: string | null;
};

export type InsuranceItem = {
    id: number;
    tracking_code: string;
    carrier: string;
    amount_cents: number;
    fee_cents: number | null;
    provider: string | null;
    status: string;
    reference: string | null;
    shipment_id: number | null;
    created_at: string;
};

export const returnsApi = {
    async list(params: Record<string, string | undefined> = {}): Promise<Paginated<ReturnItem>> {
        const { data } = await client.get('/returns', { params });
        return data;
    },
    async show(id: number): Promise<ReturnDetail> {
        const { data } = await client.get(`/returns/${id}`);
        return data;
    },
    async create(input: {
        original_shipment_id: number;
        reason?: string;
        items?: string[];
        notes?: string;
        auto_refund?: boolean;
    }): Promise<{ id: number; status: string }> {
        const { data } = await client.post('/returns', input);
        return data;
    },
    async approve(id: number): Promise<{ id: number; status: string; return_shipment_id: number | null }> {
        const { data } = await client.post(`/returns/${id}/approve`);
        return data;
    },
    async decline(id: number, reason?: string): Promise<{ id: number; status: string }> {
        const { data } = await client.post(`/returns/${id}/decline`, { reason });
        return data;
    },
};

export const claimsApi = {
    async list(params: Record<string, string | undefined> = {}): Promise<Paginated<ClaimItem>> {
        const { data } = await client.get('/claims', { params });
        return data;
    },
    async show(id: number): Promise<ClaimDetail> {
        const { data } = await client.get(`/claims/${id}`);
        return data;
    },
    async create(input: {
        shipment_id: number;
        type: 'damage' | 'loss' | 'missing_items';
        amount_cents: number;
        description: string;
        insurance_id?: number;
    }): Promise<{ id: number; state: string }> {
        const { data } = await client.post('/claims', input);
        return data;
    },
    async submit(id: number): Promise<{ id: number; state: string }> {
        const { data } = await client.post(`/claims/${id}/submit`);
        return data;
    },
    async approve(id: number, recoveredCents?: number): Promise<{ id: number; state: string; recovered_cents: number | null }> {
        const { data } = await client.post(`/claims/${id}/approve`, { recovered_cents: recoveredCents });
        return data;
    },
    async pay(id: number, recoveredCents?: number): Promise<{ id: number; state: string }> {
        const { data } = await client.post(`/claims/${id}/pay`, { recovered_cents: recoveredCents });
        return data;
    },
    async close(id: number, reason?: string): Promise<{ id: number; state: string }> {
        const { data } = await client.post(`/claims/${id}/close`, { reason });
        return data;
    },
};

export const insuranceApi = {
    async list(): Promise<Paginated<InsuranceItem>> {
        const { data } = await client.get('/insurance');
        return data;
    },
    async create(input: {
        tracking_code: string;
        carrier: string;
        amount_cents: number;
        shipment_id?: number;
        reference?: string;
    }): Promise<{ id: number; status: string; ep_insurance_id: string | null; messages: Record<string, string> | null }> {
        const { data } = await client.post('/insurance', input);
        return data;
    },
};
