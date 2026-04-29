import client from '@shared/api/client';
import type { Address, ApprovalItem, ShipmentDetail, ShipmentListItem } from '@dashboard/types/shipment';

type Paginated<T> = { data: T[]; meta: { current_page: number; last_page: number; total: number } };

export const shipmentsApi = {
    async list(params: Record<string, string | number | undefined> = {}): Promise<Paginated<ShipmentListItem>> {
        const { data } = await client.get('/shipments', { params });
        return data;
    },

    async show(id: number): Promise<ShipmentDetail> {
        const { data } = await client.get(`/shipments/${id}`);
        return data;
    },

    async create(input: {
        to_address: Address;
        from_address: Address;
        parcel: { weight_oz: number; length_in?: number; width_in?: number; height_in?: number };
        reference?: string;
        client_id?: number;
        declared_value_cents?: number;
    }): Promise<ShipmentDetail> {
        const { data } = await client.post('/shipments', input);
        return data;
    },

    async buy(id: number, rateId: string, insuranceCents?: number): Promise<{ status: string; shipment: ShipmentDetail; approval_id: number | null }> {
        const { data } = await client.post(`/shipments/${id}/buy`, { rate_id: rateId, insurance_cents: insuranceCents });
        return data;
    },

    async void(id: number, reason?: string): Promise<ShipmentDetail> {
        const { data } = await client.post(`/shipments/${id}/void`, { reason });
        return data;
    },

    async assign(id: number, assigneeId: number | null): Promise<ShipmentDetail> {
        const { data } = await client.post(`/shipments/${id}/assign`, { assignee_id: assigneeId });
        return data;
    },

    async pack(id: number): Promise<ShipmentDetail> {
        const { data } = await client.post(`/shipments/${id}/pack`);
        return data;
    },

    async myQueue(): Promise<Paginated<ShipmentListItem>> {
        const { data } = await client.get('/shipments/my-queue');
        return data;
    },

    async approvals(status = 'pending'): Promise<Paginated<ApprovalItem>> {
        const { data } = await client.get('/shipments/approvals', { params: { status } });
        return data;
    },

    async approve(approvalId: number): Promise<{ status: string; buy_status: string | null; shipment_id: number }> {
        const { data } = await client.post(`/shipments/approvals/${approvalId}/approve`);
        return data;
    },

    async decline(approvalId: number, reason?: string): Promise<void> {
        await client.post(`/shipments/approvals/${approvalId}/decline`, { reason });
    },
};
