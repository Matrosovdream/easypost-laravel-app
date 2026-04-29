<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import type { NavItem } from '@dashboard/config/nav';
import { useCountsStore } from '@dashboard/stores/counts';
import { useLayout } from '@dashboard/composables/useLayout';

const props = defineProps<{ item: NavItem }>();

const counts = useCountsStore();
const route = useRoute();
const { layoutState, isDesktop } = useLayout();

const isActive = computed(() => {
    if (!props.item.to) return false;
    if (props.item.to === '/dashboard') {
        return route.path === '/dashboard';
    }
    return route.path === props.item.to || route.path.startsWith(props.item.to + '/');
});

function badgeValue(key: NavItem['badge']): number {
    if (!key) return 0;
    return counts.counts[key] ?? 0;
}

function onClick(): void {
    if (!isDesktop()) {
        layoutState.mobileMenuActive = false;
    }
    layoutState.overlayMenuActive = false;
}
</script>

<template>
    <li>
        <router-link
            v-if="item.to"
            :to="item.to"
            custom
            v-slot="{ href, navigate }"
        >
            <a
                :href="href"
                :class="{ 'active-route': isActive }"
                @click="(e) => { onClick(); navigate(e); }"
            >
                <i v-if="item.icon" :class="`${item.icon} layout-menuitem-icon`"></i>
                <span class="layout-menuitem-text">{{ item.label }}</span>
                <span v-if="item.badge && badgeValue(item.badge) > 0" class="layout-menu-badge">
                    {{ badgeValue(item.badge) }}
                </span>
            </a>
        </router-link>
    </li>
</template>
