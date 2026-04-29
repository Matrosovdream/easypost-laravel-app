import { defineStore } from 'pinia';
import client from '@shared/api/client';

type Counts = {
    approvalsCount: number;
    exceptionsCount: number;
    returnsCount: number;
    claimsCount: number;
    queueCount: number;
    printReady: number;
};

const empty: Counts = {
    approvalsCount: 0,
    exceptionsCount: 0,
    returnsCount: 0,
    claimsCount: 0,
    queueCount: 0,
    printReady: 0,
};

export const useCountsStore = defineStore('counts', {
    state: (): { counts: Counts } => ({ counts: { ...empty } }),
    actions: {
        async fetch() {
            try {
                const { data } = await client.get<Counts>('/navigation/counts');
                this.counts = { ...empty, ...data };
            } catch {
                this.counts = { ...empty };
            }
        },
        set(partial: Partial<Counts>) {
            this.counts = { ...this.counts, ...partial };
        },
    },
});
