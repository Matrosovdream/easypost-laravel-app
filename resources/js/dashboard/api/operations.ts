import client from '@shared/api/client';

type Paginated<T> = { data: T[]; meta: { current_page: number; last_page: number; total: number } };

export type BatchListItem = {
    id: number;
    reference: string | null;
    state: string;
    num_shipments: number;
    label_url: string | null;
    created_by: { id: number; name: string } | null;
    created_at: string;
};

export type BatchDetail = BatchListItem & {
    status_summary: Record<string, unknown> | null;
    scan_form_id: number | null;
    pickup_id: number | null;
    shipments: Array<{
        id: number;
        status: string;
        carrier: string | null;
        service: string | null;
        tracking_code: string | null;
        reference: string | null;
        batch_status: string | null;
        batch_message: string | null;
        to_address: { city: string | null; state: string | null; country: string | null } | null;
    }>;
};

export type ScanFormItem = {
    id: number;
    carrier: string;
    status: string;
    form_url: string | null;
    tracking_codes: string[] | null;
    created_at: string;
};

export type PickupItem = {
    id: number;
    reference: string | null;
    status: string;
    carrier: string | null;
    service: string | null;
    confirmation: string | null;
    min_datetime: string;
    max_datetime: string;
    cost_cents: number | null;
    address: { name: string | null; city: string | null; state: string | null } | null;
};

export type PickupDetail = PickupItem & {
    instructions: string | null;
    rates: Array<{ id?: string; carrier: string; service: string; rate: string | number }> | null;
    address: {
        id: number;
        name: string | null;
        street1: string;
        city: string | null;
        state: string | null;
        zip: string | null;
        country: string;
    } | null;
};

export const batchesApi = {
    async list(params: Record<string, string | undefined> = {}): Promise<Paginated<BatchListItem>> {
        const { data } = await client.get('/batches', { params });
        return data;
    },
    async show(id: number): Promise<BatchDetail> {
        const { data } = await client.get(`/batches/${id}`);
        return data;
    },
    async create(input: { shipment_ids: number[]; reference?: string }): Promise<{ id: number; state: string }> {
        const { data } = await client.post('/batches', input);
        return data;
    },
    async buy(id: number): Promise<{ id: number; state: string }> {
        const { data } = await client.post(`/batches/${id}/buy`);
        return data;
    },
    async labels(id: number): Promise<{ id: number; label_url: string | null }> {
        const { data } = await client.post(`/batches/${id}/labels`);
        return data;
    },
};

export const scanFormsApi = {
    async list(): Promise<Paginated<ScanFormItem>> {
        const { data } = await client.get('/scan-forms');
        return data;
    },
    async create(shipmentIds: number[]): Promise<{ id: number; status: string; form_url: string | null }> {
        const { data } = await client.post('/scan-forms', { shipment_ids: shipmentIds });
        return data;
    },
};

export const pickupsApi = {
    async list(params: Record<string, string | undefined> = {}): Promise<Paginated<PickupItem>> {
        const { data } = await client.get('/pickups', { params });
        return data;
    },
    async show(id: number): Promise<PickupDetail> {
        const { data } = await client.get(`/pickups/${id}`);
        return data;
    },
    async schedule(input: {
        address_id: number;
        min_datetime: string;
        max_datetime: string;
        instructions?: string;
        reference?: string;
        is_account_address?: boolean;
    }): Promise<{ id: number; status: string; rates: unknown[] | null }> {
        const { data } = await client.post('/pickups', input);
        return data;
    },
    async buy(id: number, carrier: string, service: string): Promise<{ id: number; status: string; confirmation: string | null }> {
        const { data } = await client.post(`/pickups/${id}/buy`, { carrier, service });
        return data;
    },
    async cancel(id: number): Promise<{ id: number; status: string }> {
        const { data } = await client.post(`/pickups/${id}/cancel`);
        return data;
    },
};
