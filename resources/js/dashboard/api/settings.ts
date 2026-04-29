import client from '@shared/api/client';

export type TeamResp = {
    id: number;
    name: string;
    plan: string;
    mode: string;
    status: string;
    time_zone: string;
    default_currency: string;
    settings: Record<string, unknown> | null;
    logo_s3_key: string | null;
};

export type TeamUser = {
    id: number;
    name: string;
    email: string;
    role_slug: string | null;
    role_name: string | null;
    is_active: boolean;
    last_login_at: string | null;
    spending_cap_cents: number | null;
    daily_cap_cents: number | null;
    client_id: number | null;
    membership_status: string | null;
};

export type ManagerRow = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    membership_status: string | null;
    last_login_at: string | null;
    joined_at: string | null;
    created_at: string | null;
    shipments_assigned: number;
    shipments_approved: number;
    shipments_approved_30d: number;
    approvals_pending: number;
};

export type PersonRow = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    membership_status: string | null;
    last_login_at: string | null;
    joined_at: string | null;
    created_at: string | null;
    [statKey: string]: number | string | boolean | null;
};

export type PeopleColumn = {
    key: string;
    label: string;
    severity_when_gt0?: 'warn' | 'danger' | 'info';
};

export type PeopleResponse = {
    role: string;
    columns: PeopleColumn[];
    data: PersonRow[];
};

export type AuditEntry = {
    id: number;
    action: string;
    user: { name: string; email: string } | null;
    subject_type: string | null;
    subject_id: number | null;
    meta: Record<string, unknown> | null;
    ip: string | null;
    created_at: string;
};

export const settingsApi = {
    async team(): Promise<TeamResp> {
        const { data } = await client.get('/settings/team');
        return data;
    },
    async updateTeam(payload: Partial<TeamResp>): Promise<{ ok: boolean }> {
        const { data } = await client.put('/settings/team', payload);
        return data;
    },
    async users(): Promise<{ data: TeamUser[] }> {
        const { data } = await client.get('/settings/users');
        return data;
    },
    async managers(): Promise<{ data: ManagerRow[] }> {
        const { data } = await client.get('/settings/managers');
        return data;
    },
    async peopleByRole(role: string): Promise<PeopleResponse> {
        const { data } = await client.get(`/settings/people/${role}`);
        return data;
    },
    async inviteUser(input: {
        email: string;
        role_slug: string;
        client_id?: number;
        spending_cap_cents?: number;
        daily_cap_cents?: number;
        notes?: string;
    }): Promise<{ id: number; token: string; expires_at: string }> {
        const { data } = await client.post('/settings/users/invite', input);
        return data;
    },
    async changeRole(id: number, roleSlug: string): Promise<{ ok: boolean }> {
        const { data } = await client.post(`/settings/users/${id}/role`, { role_slug: roleSlug });
        return data;
    },
    async disableUser(id: number): Promise<{ ok: boolean }> {
        const { data } = await client.post(`/settings/users/${id}/disable`);
        return data;
    },
    async enableUser(id: number): Promise<{ ok: boolean }> {
        const { data } = await client.post(`/settings/users/${id}/enable`);
        return data;
    },
    async regeneratePin(id: number): Promise<{ pin: string }> {
        const { data } = await client.post(`/settings/users/${id}/pin`);
        return data;
    },
    async auditLog(params: Record<string, string | number | undefined> = {}): Promise<{ data: AuditEntry[]; meta: { current_page: number; last_page: number; total: number } }> {
        const { data } = await client.get('/settings/audit-log', { params });
        return data;
    },
};
