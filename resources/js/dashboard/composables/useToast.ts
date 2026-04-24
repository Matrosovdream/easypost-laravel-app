import { useToast as usePvToast } from 'primevue/usetoast';

type Severity = 'success' | 'info' | 'warn' | 'error';

export function useToast() {
    const t = usePvToast();

    const show = (severity: Severity, summary: string, detail?: string, life = 3500): void => {
        t.add({ severity, summary, detail, life });
    };

    return {
        success: (summary: string, detail?: string) => show('success', summary, detail),
        info: (summary: string, detail?: string) => show('info', summary, detail),
        warn: (summary: string, detail?: string) => show('warn', summary, detail),
        error: (summary: string, detail?: string) => show('error', summary, detail, 6000),
    };
}
