import { defineStore } from 'pinia';

export type NotificationItem = {
    id: string;
    title: string;
    body?: string;
    severity?: 'info' | 'success' | 'warn' | 'error';
    read: boolean;
    occurred_at: string;
    link?: string | null;
};

type State = {
    items: NotificationItem[];
    unreadCount: number;
};

export const useNotificationsStore = defineStore('notifications', {
    state: (): State => ({
        items: [],
        unreadCount: 0,
    }),
    actions: {
        push(item: NotificationItem) {
            this.items.unshift(item);
            if (this.items.length > 10) this.items.length = 10;
            if (!item.read) this.unreadCount += 1;
        },
        markAllRead() {
            this.items = this.items.map((i) => ({ ...i, read: true }));
            this.unreadCount = 0;
        },
        clear() {
            this.items = [];
            this.unreadCount = 0;
        },
    },
});
