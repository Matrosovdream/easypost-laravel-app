import client from '@shared/api/client';

type Paginated<T> = { data: T[]; meta: { current_page: number; last_page: number; total: number } };

export type AddressItem = {
    id: number;
    name: string | null;
    company: string | null;
    street1: string;
    street2: string | null;
    city: string | null;
    state: string | null;
    zip: string | null;
    country: string;
    phone: string | null;
    email: string | null;
    residential: boolean | null;
    verified: boolean;
    verified_at: string | null;
    ep_address_id: string | null;
    client_id: number | null;
    created_at: string;
};

export type TrackerItem = {
    id: number;
    tracking_code: string;
    carrier: string;
    status: string;
    status_detail: string | null;
    est_delivery_date: string | null;
    last_event_at: string | null;
    public_url: string | null;
    shipment_id: number | null;
    created_at: string;
};

export type TrackerDetail = TrackerItem & {
    events: Array<{
        status: string;
        status_detail: string | null;
        message: string;
        source: string | null;
        event_datetime: string;
        location: Record<string, string | null> | null;
    }>;
};

export const addressesApi = {
    async list(params: Record<string, string | undefined> = {}): Promise<Paginated<AddressItem>> {
        const { data } = await client.get('/addresses', { params });
        return data;
    },
    async show(id: number): Promise<AddressItem> {
        const { data } = await client.get(`/addresses/${id}`);
        return data;
    },
    async create(input: Partial<AddressItem> & { street1: string; country: string; verify?: boolean }): Promise<AddressItem> {
        const { data } = await client.post('/addresses', input);
        return data;
    },
    async verify(id: number): Promise<AddressItem> {
        const { data } = await client.post(`/addresses/${id}/verify`);
        return data;
    },
    async delete(id: number): Promise<void> {
        await client.delete(`/addresses/${id}`);
    },
};

export const trackersApi = {
    async list(params: Record<string, string | undefined> = {}): Promise<Paginated<TrackerItem>> {
        const { data } = await client.get('/trackers', { params });
        return data;
    },
    async show(id: number): Promise<TrackerDetail> {
        const { data } = await client.get(`/trackers/${id}`);
        return data;
    },
    async create(trackingCode: string, carrier: string): Promise<TrackerItem> {
        const { data } = await client.post('/trackers', { tracking_code: trackingCode, carrier });
        return data;
    },
    async delete(id: number): Promise<void> {
        await client.delete(`/trackers/${id}`);
    },
};
