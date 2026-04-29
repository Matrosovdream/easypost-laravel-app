import client from '@shared/api/client';

export const profileApi = {
    async update(payload: { name?: string; phone?: string; locale?: string; timezone?: string }): Promise<{ ok: boolean }> {
        const { data } = await client.put('/profile', payload);
        return data;
    },
    async changePin(currentPin: string, newPin: string): Promise<{ ok: boolean }> {
        const { data } = await client.post('/profile/pin', {
            current_pin: currentPin,
            new_pin: newPin,
            new_pin_confirmation: newPin,
        });
        return data;
    },
    async sessions(): Promise<{ data: Array<{ id: string; user_agent: string; ip: string; last_activity: string; current: boolean }> }> {
        const { data } = await client.get('/profile/sessions');
        return data;
    },
    async notifications(): Promise<{ data: Record<string, boolean> }> {
        const { data } = await client.get('/profile/notifications');
        return data;
    },
    async updateNotifications(prefs: Record<string, boolean>): Promise<{ data: Record<string, boolean> }> {
        const { data } = await client.put('/profile/notifications', { prefs });
        return data;
    },
};
