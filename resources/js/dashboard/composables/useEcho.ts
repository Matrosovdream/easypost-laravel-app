import { onMounted, onBeforeUnmount } from 'vue';
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
    REVERB_PORT?: string;
    REVERB_SCHEME?: string;
};

function ensureEcho(): Echo<'reverb'> | null {
    const w = window as EchoWindow;
    if (w.Echo) return w.Echo;

    // Read Reverb public config from a JSON blob rendered in the blade template
    // under <meta name="reverb" content='...'>. Fallback to env placeholders.
    const meta = document.querySelector<HTMLMetaElement>('meta[name="reverb"]');
    if (!meta?.content) return null;
    let cfg: ReverbEnv;
    try { cfg = JSON.parse(meta.content) as ReverbEnv; } catch { return null; }
    if (!cfg.REVERB_APP_KEY) return null;

    w.Pusher = Pusher;
    w.Echo = new Echo({
        broadcaster: 'reverb',
        key: cfg.REVERB_APP_KEY,
        wsHost: cfg.REVERB_HOST ?? window.location.hostname,
        wsPort: Number(cfg.REVERB_PORT ?? 8080),
        wssPort: Number(cfg.REVERB_PORT ?? 8080),
        forceTLS: (cfg.REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
    return w.Echo;
}

// Exported so unit tests and the dashboard can use it; safely no-ops when
// Reverb isn't wired (e.g. in CI without WebSocket infra).
export function useEcho() {
    const auth = useAuthStore();
    const counts = useCountsStore();
    const notifications = useNotificationsStore();

    const teamId = auth.user?.current_team?.id ?? null;
    let ch: ReturnType<Echo<'reverb'>['private']> | null = null;

    onMounted(() => {
        if (!teamId) return;
        const echo = ensureEcho();
        if (!echo) return;

        ch = echo.private(`team.${teamId}`);
        ch.listen('.shipment.updated', () => counts.fetch());
        ch.listen('.tracker.updated', () => counts.fetch());
        ch.listen('.approval.requested', (payload: { id: number; shipment_id: number; cost_cents: number }) => {
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
        ch.listen('.counts.updated', () => counts.fetch());
    });

    onBeforeUnmount(() => {
        if (!ch) return;
        const w = window as EchoWindow;
        if (w.Echo && teamId) w.Echo.leave(`team.${teamId}`);
        ch = null;
    });
}
