<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MarketingLayout from '@web/layouts/MarketingLayout.vue';
import PortalLayout from '@web/layouts/PortalLayout.vue';
import TrackingLayout from '@web/layouts/TrackingLayout.vue';

const route = useRoute();
const layout = computed(() => {
    switch (route.meta.layout) {
        case 'portal':   return PortalLayout;
        case 'tracking': return TrackingLayout;
        default:         return MarketingLayout;
    }
});
</script>

<template>
    <component :is="layout">
        <router-view />
    </component>
</template>
