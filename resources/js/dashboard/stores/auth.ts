import { defineStore } from 'pinia';
import client from '@shared/api/client';

export type Role = { id: number; slug: string; name: string };
export type CurrentTeam = {
    id: number;
    name: string;
    plan: string;
    mode: string;
    status: string;
} | null;

export type CurrentUser = {
    id: number;
    email: string;
    name: string;
    avatar?: string | null;
    locale?: string | null;
    timezone?: string | null;
    is_active: boolean;
    roles: Role[];
    permissions: string[];
    current_team: CurrentTeam;
};

type State = {
    user: CurrentUser | null;
    loading: boolean;
    loaded: boolean;
};

const CACHE_KEY = 'shipdesk.me';

function readCache(): CurrentUser | null {
    try {
        const raw = sessionStorage.getItem(CACHE_KEY);
        return raw ? (JSON.parse(raw) as CurrentUser) : null;
    } catch {
        return null;
    }
}

function writeCache(user: CurrentUser | null): void {
    try {
        if (user) sessionStorage.setItem(CACHE_KEY, JSON.stringify(user));
        else sessionStorage.removeItem(CACHE_KEY);
    } catch {
        // ignore storage errors
    }
}

export const useAuthStore = defineStore('auth', {
    state: (): State => ({ user: null, loading: false, loaded: false }),
    getters: {
        isAuthenticated: (s) => s.user !== null,
        primaryRole: (s): string => s.user?.roles[0]?.slug ?? 'viewer',
        teamStatus: (s): string => s.user?.current_team?.status ?? 'active',
        can: (s) => (right: string): boolean => s.user?.permissions.includes(right) ?? false,
        canAny: (s) => (rights: string[]): boolean =>
            rights.some((r) => s.user?.permissions.includes(r)),
    },
    actions: {
        setUser(user: CurrentUser | null) {
            this.user = user;
            writeCache(user);
        },
        hydrateFromCache(): boolean {
            const cached = readCache();
            if (cached) {
                this.user = cached;
                this.loaded = true;
                return true;
            }
            return false;
        },
        async fetchMe() {
            if (this.loading) return;
            this.loading = true;

            // Seed from session cache first so the dashboard doesn't bounce while
            // the server-side session cookie is still propagating right after login.
            if (!this.user) this.hydrateFromCache();

            try {
                const { data } = await client.get<{ user: CurrentUser }>('/auth/me');
                this.setUser(data.user);
                this.loaded = true;
            } catch {
                // Only drop the user if we don't already have a cached one from
                // the login response. This avoids a false logout when the session
                // cookie hasn't been picked up yet by the current request.
                if (!this.user) {
                    this.user = null;
                    writeCache(null);
                }
                this.loaded = true;
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                await client.post('/auth/logout');
            } catch {
                // swallow — we're logging out anyway
            }
            this.setUser(null);
            window.location.href = '/portal/login';
        },
    },
});
