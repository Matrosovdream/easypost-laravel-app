<script setup lang="ts">
import { ref, computed } from 'vue';
import Menu from 'primevue/menu';
import Tag from 'primevue/tag';
import NotificationsBell from './NotificationsBell.vue';
import CommandPalette from './CommandPalette.vue';
import { useLayout } from '@dashboard/composables/useLayout';
import { useAuthStore } from '@dashboard/stores/auth';

const { toggleMenu, toggleDarkMode, toggleConfigSidebar, isDarkTheme } = useLayout();
const auth = useAuthStore();

const paletteRef = ref<InstanceType<typeof CommandPalette> | null>(null);
const userMenuRef = ref<InstanceType<typeof Menu> | null>(null);

const roleLabel = computed(() => auth.user?.roles[0]?.name ?? 'Viewer');
const initials = computed(() => (auth.user?.name ?? '?').slice(0, 1).toUpperCase());

const userMenuItems = [
    { label: 'Profile', icon: 'pi pi-user', command: () => { window.location.href = '/dashboard/profile'; } },
    { label: 'Change PIN', icon: 'pi pi-lock', command: () => { window.location.href = '/dashboard/profile#pin'; } },
    { separator: true },
    { label: 'Sign out', icon: 'pi pi-sign-out', command: () => { void auth.logout(); } },
];

function openPalette(): void { paletteRef.value?.open(); }
function openUserMenu(e: Event): void { userMenuRef.value?.toggle(e); }
</script>

<template>
    <header class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button class="layout-menu-button" aria-label="Toggle menu" @click="toggleMenu">
                <i class="pi pi-bars"></i>
            </button>
            <router-link to="/dashboard" class="layout-topbar-logo">
                <span class="brand-mark"><i class="pi pi-send"></i></span>
                <span>ShipDesk</span>
            </router-link>
        </div>

        <div class="layout-topbar-search">
            <button type="button" @click="openPalette">
                <i class="pi pi-search"></i>
                <span>Search shipments, clients, pages…</span>
                <kbd>⌘K</kbd>
            </button>
        </div>

        <div class="layout-topbar-actions">
            <Tag v-if="auth.user" :value="roleLabel" severity="info" class="hidden sm:inline-flex" />

            <div class="layout-config-menu">
                <button type="button" class="layout-topbar-action" aria-label="Toggle dark mode" @click="toggleDarkMode">
                    <i :class="['pi', isDarkTheme ? 'pi-moon' : 'pi-sun']"></i>
                </button>
                <button type="button" class="layout-topbar-action layout-topbar-action-highlight" aria-label="Theme configurator" @click="toggleConfigSidebar">
                    <i class="pi pi-palette"></i>
                </button>
            </div>

            <NotificationsBell />

            <button
                type="button"
                class="layout-topbar-action"
                style="width: auto; padding: 0 0.75rem; gap: 0.5rem; display: inline-flex; border-radius: 9999px;"
                @click="openUserMenu"
            >
                <span
                    style="width: 2rem; height: 2rem; border-radius: 9999px; background: var(--p-primary-100); color: var(--p-primary-700); font-weight: 600; display: inline-flex; align-items: center; justify-content: center;"
                >{{ initials }}</span>
                <span class="hidden md:inline" style="font-size: 0.875rem; font-weight: 500;">{{ auth.user?.name ?? 'User' }}</span>
                <i class="pi pi-chevron-down" style="font-size: 0.6rem;"></i>
            </button>
            <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" />
        </div>

        <CommandPalette ref="paletteRef" />
    </header>
</template>
