<script setup lang="ts">
import { computed } from 'vue';
import AppSidebar from '@dashboard/components/shell/AppSidebar.vue';
import AppTopbar from '@dashboard/components/shell/AppTopbar.vue';
import AppFooter from '@dashboard/components/shell/AppFooter.vue';
import AppConfigurator from '@dashboard/components/shell/AppConfigurator.vue';
import { useLayout } from '@dashboard/composables/useLayout';
import { useEcho } from '@dashboard/composables/useEcho';

const { layoutConfig, layoutState, hideMobileMenu } = useLayout();

useEcho();

const containerClass = computed(() => ({
    'layout-overlay': layoutConfig.menuMode === 'overlay',
    'layout-static': layoutConfig.menuMode === 'static',
    'layout-overlay-active': layoutState.overlayMenuActive,
    'layout-mobile-active': layoutState.mobileMenuActive,
    'layout-static-inactive': layoutState.staticMenuInactive,
}));
</script>

<template>
    <div class="layout-wrapper" :class="containerClass">
        <AppTopbar />
        <AppSidebar />
        <div class="layout-main-container">
            <main class="layout-main">
                <slot />
            </main>
            <AppFooter />
        </div>
        <div class="layout-mask" @click="hideMobileMenu"></div>
        <AppConfigurator />
    </div>
</template>
