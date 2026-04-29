import { useAuthStore } from '@dashboard/stores/auth';

export function useCan() {
    const auth = useAuthStore();
    return {
        can: (right: string): boolean => auth.can(right),
        canAny: (rights: string[]): boolean => auth.canAny(rights),
        primaryRole: (): string => auth.primaryRole,
    };
}
