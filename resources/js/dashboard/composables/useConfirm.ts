import { useConfirm as usePvConfirm } from 'primevue/useconfirm';

type ConfirmOpts = {
    title?: string;
    message: string;
    acceptLabel?: string;
    rejectLabel?: string;
    severity?: 'danger' | 'primary' | 'secondary';
    onAccept?: () => void | Promise<void>;
    onReject?: () => void;
};

export function useConfirm() {
    const pv = usePvConfirm();

    return {
        require: (opts: ConfirmOpts): void => {
            pv.require({
                header: opts.title ?? 'Are you sure?',
                message: opts.message,
                acceptLabel: opts.acceptLabel ?? 'Confirm',
                rejectLabel: opts.rejectLabel ?? 'Cancel',
                acceptProps: { severity: opts.severity ?? 'primary' },
                accept: () => { void opts.onAccept?.(); },
                reject: () => opts.onReject?.(),
            });
        },
    };
}
