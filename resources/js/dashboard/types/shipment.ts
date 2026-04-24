export type ShipmentStatus =
    | 'requested'
    | 'rated'
    | 'pending_approval'
    | 'purchased'
    | 'packed'
    | 'delivered'
    | 'voided'
    | 'rate_declined'
    | 'rate_failed'
    | 'failed';

export type ShipmentListItem = {
    id: number;
    status: ShipmentStatus | string;
    carrier: string | null;
    service: string | null;
    tracking_code: string | null;
    cost_cents: number | null;
    reference: string | null;
    client_id: number | null;
    assigned_to: number | null;
    requested_by: number | null;
    to_address: {
        name: string | null;
        city: string | null;
        state: string | null;
        country: string | null;
        zip: string | null;
    } | null;
    created_at: string;
};

export type Rate = {
    id: string;
    carrier: string;
    service: string;
    rate: string | number;
    currency?: string;
    delivery_days?: number | null;
    retail_rate?: string | number | null;
};

export type Address = {
    id?: number;
    name?: string | null;
    company?: string | null;
    street1?: string;
    street2?: string | null;
    city?: string | null;
    state?: string | null;
    zip?: string | null;
    country?: string;
    phone?: string | null;
    email?: string | null;
};

export type ShipmentDetail = {
    id: number;
    status: string;
    status_detail: string | null;
    reference: string | null;
    carrier: string | null;
    service: string | null;
    tracking_code: string | null;
    cost_cents: number | null;
    insurance_cents: number | null;
    declared_value_cents: number | null;
    is_return: boolean;
    client_id: number | null;
    ep_shipment_id: string | null;
    label_url: string | null;
    to_address: Address | null;
    from_address: Address | null;
    parcel: {
        weight_oz: number;
        length_in: number | null;
        width_in: number | null;
        height_in: number | null;
        predefined_package: string | null;
    } | null;
    rates: Rate[];
    selected_rate: Rate | null;
    options: Record<string, unknown> | null;
    messages: Record<string, unknown> | null;
    assigned_to: { id: number; name: string } | null;
    requested_by: { id: number; name: string } | null;
    events: Array<{ type: string; payload: Record<string, unknown> | null; created_at: string }>;
    approved_at: string | null;
    packed_at: string | null;
    created_at: string;
};

export type ApprovalItem = {
    id: number;
    status: string;
    cost_cents: number;
    reason: string;
    note: string | null;
    rate_snapshot: Rate | null;
    requested_by: { id: number; name: string } | null;
    approver: { id: number; name: string } | null;
    shipment_id: number;
    shipment: {
        id: number;
        reference: string | null;
        status: string;
        to_address: { city: string | null; state: string | null; country: string } | null;
    } | null;
    created_at: string;
    resolved_at: string | null;
};
