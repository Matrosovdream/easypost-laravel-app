import { onBeforeUnmount, onMounted, watch } from 'vue';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { useAuthStore } from '@dashboard/stores/auth';
import { useCountsStore } from '@dashboard/stores/counts';
import { useNotificationsStore } from '@dashboard/stores/notifications';

type EchoWindow = Window & {
    Echo?: Echo<'reverb'>;
    Pusher?: typeof Pusher;
};

type ReverbEnv = {
    REVERB_APP_KEY?: string;
    REVERB_HOST?: string;
    REVERB_PORT?: string | number;
    REVERB_SCHEME?: string;
};

function ensureEcho(): Echo<'reverb'> | null {
    const w = window as EchoWindow;
    if (w.Echo) return w.Echo;

    const meta = document.querySelector<HTMLMetaElement>('meta[name="reverb"]');
    if (!meta?.content) {
        console.warn('[useEcho] <meta name="reverb"> missing — Reverb disabled.');
        return null;
    }

    let cfg: ReverbEnv;
    try {
        cfg = JSON.parse(meta.content) as ReverbEnv;
    } catch (e) {
        console.warn('[useEcho] failed to parse <meta name="reverb"> JSON:', e);
        return null;
    }

    if (!cfg.REVERB_APP_KEY) {
        console.warn('[useEcho] REVERB_APP_KEY missing in meta — Reverb disabled.');
        return null;
    }

    const port = Number(cfg.REVERB_PORT ?? 8080);
    const forceTLS = (cfg.REVERB_SCHEME ?? 'https') === 'https';

    w.Pusher = Pusher;
    try {
        w.Echo = new Echo({
            broadcaster: 'reverb',
            key: cfg.REVERB_APP_KEY,
            wsHost: cfg.REVERB_HOST ?? window.location.hostname,
            wsPort: port,
            wssPort: port,
            forceTLS,
            enabledTransports: ['ws', 'wss'],
        });
    } catch (e) {
        console.error('[useEcho] failed to init Echo:', e);
        return null;
    }

    return w.Echo ?? null;
}

export function useEcho() {
    const auth = useAuthStore();
    const counts = useCountsStore();
    const notifications = useNotificationsStore();

    let echo: Echo<'reverb'> | null = null;
    let teamCh: ReturnType<Echo<'reverb'>['private']> | null = null;
    let demoCh: ReturnType<Echo<'reverb'>['channel']> | null = null;
    let activeTeamId: number | null = null;
    let demoSubscribed = false;

    onMounted(() => {
        echo = ensureEcho();
    });

    const stop = watch(
        () => auth.user,
        (user) => {
            if (!user || !echo) return;

            const teamId = user.current_team?.id ?? null;
            if (teamId && teamId !== activeTeamId) {
                if (activeTeamId !== null) echo.leave(`team.${activeTeamId}`);
                activeTeamId = teamId;
                teamCh = echo.private(`team.${teamId}`);
                teamCh.listen('.shipment.updated', () => counts.fetch());
                teamCh.listen('.tracker.updated', () => counts.fetch());
                teamCh.listen('.approval.requested', (payload: { id: number; shipment_id: number; cost_cents: number }) => {
                    notifications.push({
                        id: `approval-${payload.id}`,
                        title: 'Approval requested',
                        body: `Shipment #${payload.shipment_id} · $${(payload.cost_cents / 100).toFixed(2)}`,
                        severity: 'warn',
                        read: false,
                        occurred_at: new Date().toISOString(),
                        link: `/dashboard/shipments/${payload.shipment_id}`,
                    });
                    counts.fetch();
                });
                teamCh.listen('.counts.updated', () => counts.fetch());
            }

            const isAdmin = user.roles.some((r) => r.slug === 'admin');
            if (isAdmin && !demoSubscribed) {
                demoSubscribed = true;
                demoCh = echo.channel('demo.features');
                demoCh.listen('.features.visited', (payload: { visitor_id: string; user_agent?: string | null; occurred_at: string }) => {
                    notifications.push({
                        id: `feature-visit-${payload.visitor_id}`,
                        title: 'Visitor on /features',
                        body: payload.user_agent ?? 'Anonymous visitor',
                        severity: 'info',
                        read: false,
                        occurred_at: payload.occurred_at,
                        link: '/features',
                    });
                });
            }
        },
        { immediate: true, flush: 'post' },
    );

    onBeforeUnmount(() => {
        stop();
        const w = window as EchoWindow;
        if (teamCh && w.Echo && activeTeamId !== null) w.Echo.leave(`team.${activeTeamId}`);
        if (demoCh && w.Echo) w.Echo.leave('demo.features');
        teamCh = null;
        demoCh = null;
        activeTeamId = null;
        demoSubscribed = false;
        echo = null;
    });
}
